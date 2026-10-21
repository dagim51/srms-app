<?php

namespace App\Services\GIS;

use App\Models\ListingSearchIndex;
use App\Models\Property;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdvancedGIS
{
    /**
     * Spatial search with PostGIS - find properties within radius
     */
    public function searchWithinRadius(
        float $latitude,
        float $longitude,
        float $radiusKm,
        array $filters = [],
        int $limit = 50
    ): Collection {
        $cacheKey = "gis_radius_{$latitude}_{$longitude}_{$radiusKm}_".md5(json_encode($filters));

        return Cache::remember($cacheKey, 300, function () use ($latitude, $longitude, $radiusKm, $filters, $limit) {
            $query = ListingSearchIndex::query()
                ->where('status', 'published')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereNotNull('price')
                ->where('price', '>', 0)
                ->selectRaw('*, ST_Distance(
                    ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography,
                    ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography
                ) as distance_meters', [$longitude, $latitude])
                ->whereRaw('
                    ST_DWithin(
                        ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography,
                        ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                        ?
                    )
                ', [$longitude, $latitude, $radiusKm * 1000]);

            // Apply filters
            $this->applyFilters($query, $filters);

            return $query->orderBy('distance_meters')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    $item->distance_km = round($item->distance_meters / 1000, 2);

                    return $item;
                });
        });
    }

    /**
     * Polygon search - find properties within a custom boundary
     */
    public function searchWithinPolygon(array $coordinates, array $filters = [], int $limit = 50): Collection
    {
        // Build WKT polygon from coordinates
        $points = [];
        foreach ($coordinates as $coord) {
            $points[] = "{$coord[0]} {$coord[1]}";
        }
        // Close the polygon
        $points[] = $coordinates[0][0].' '.$coordinates[0][1];
        $wkt = 'POLYGON(('.implode(', ', $points).'))';

        $query = ListingSearchIndex::query()
            ->where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw('
                ST_Contains(
                    ST_GeomFromText(?, 4326),
                    ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
                )
            ', [$wkt]);

        $this->applyFilters($query, $filters);

        return $query->limit($limit)->get();
    }

    /**
     * K-nearest neighbors search
     */
    public function kNearestNeighbors(float $latitude, float $longitude, int $k = 10, array $filters = []): Collection
    {
        $query = ListingSearchIndex::query()
            ->where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw('*, ST_Distance(
                ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography,
                ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography
            ) as distance_meters', [$longitude, $latitude]);

        $this->applyFilters($query, $filters);

        return $query->orderBy('distance_meters')
            ->limit($k)
            ->get()
            ->map(function ($item) {
                $item->distance_km = round($item->distance_meters / 1000, 2);

                return $item;
            });
    }

    /**
     * Heatmap / density analysis for an area
     */
    public function getDensityHeatmap(
        float $minLat,
        float $minLon,
        float $maxLat,
        float $maxLon,
        int $gridSize = 100
    ): array {
        $cacheKey = "heatmap_{$minLat}_{$minLon}_{$maxLat}_{$maxLon}_{$gridSize}";

        return Cache::remember($cacheKey, 3600, function () use ($minLat, $minLon, $maxLat, $maxLon, $gridSize) {
            // Create grid cells
            $latStep = ($maxLat - $minLat) / $gridSize;
            $lonStep = ($maxLon - $minLon) / $gridSize;

            $heatmap = [];

            for ($i = 0; $i < $gridSize; $i++) {
                for ($j = 0; $j < $gridSize; $j++) {
                    $cellMinLat = $minLat + $i * $latStep;
                    $cellMaxLat = $minLat + ($i + 1) * $latStep;
                    $cellMinLon = $minLon + $j * $lonStep;
                    $cellMaxLon = $minLon + ($j + 1) * $lonStep;

                    $count = ListingSearchIndex::query()
                        ->where('status', 'published')
                        ->whereBetween('latitude', [$cellMinLat, $cellMaxLat])
                        ->whereBetween('longitude', [$cellMinLon, $cellMaxLon])
                        ->count();

                    if ($count > 0) {
                        $avgPrice = ListingSearchIndex::query()
                            ->where('status', 'published')
                            ->whereBetween('latitude', [$cellMinLat, $cellMaxLat])
                            ->whereBetween('longitude', [$cellMinLon, $cellMaxLon])
                            ->avg('price');

                        $heatmap[] = [
                            'cell' => [$i, $j],
                            'bounds' => [
                                'min_lat' => round($cellMinLat, 6),
                                'max_lat' => round($cellMaxLat, 6),
                                'min_lon' => round($cellMinLon, 6),
                                'max_lon' => round($cellMaxLon, 6),
                            ],
                            'center' => [
                                'lat' => round($cellMinLat + $latStep / 2, 6),
                                'lon' => round($cellMinLon + $lonStep / 2, 6),
                            ],
                            'count' => $count,
                            'avg_price' => round($avgPrice ?? 0),
                            'intensity' => $count,
                        ];
                    }
                }
            }

            return [
                'bounds' => [
                    'min_lat' => $minLat,
                    'max_lat' => $maxLat,
                    'min_lon' => $minLon,
                    'max_lon' => $maxLon,
                ],
                'grid_size' => $gridSize,
                'cells' => $heatmap,
                'max_intensity' => max(array_column($heatmap, 'intensity') ?? [0]),
                'generated_at' => now()->toISOString(),
            ];
        });
    }

    /**
     * Cluster analysis - find property clusters using DBSCAN-like approach
     */
    public function findClusters(float $minLat, float $minLon, float $maxLat, float $maxLon, float $epsKm = 1, int $minPoints = 3): array
    {
        $cacheKey = "clusters_{$minLat}_{$minLon}_{$maxLat}_{$maxLon}_{$epsKm}_{$minPoints}";

        return Cache::remember($cacheKey, 3600, function () use ($minLat, $minLon, $maxLat, $maxLon, $epsKm, $minPoints) {
            $listings = ListingSearchIndex::query()
                ->where('status', 'published')
                ->whereBetween('latitude', [$minLat, $maxLat])
                ->whereBetween('longitude', [$minLon, $maxLon])
                ->whereNotNull('price')
                ->get(['id', 'latitude', 'longitude', 'price', 'bedrooms', 'property_type_code', 'city'])
                ->toArray();

            if (count($listings) < $minPoints) {
                return ['clusters' => [], 'noise' => $listings];
            }

            // Simple grid-based clustering (DBSCAN approximation)
            $clusters = [];
            $visited = [];
            $clusterId = 0;
            $epsMeters = $epsKm * 1000;

            foreach ($listings as $i => $point) {
                if (isset($visited[$i])) {
                    continue;
                }

                $visited[$i] = true;
                $neighbors = $this->regionQuery($listings, $i, $epsMeters);

                if (count($neighbors) < $minPoints) {
                    continue; // Noise point
                }

                $clusterId++;
                $cluster = $this->expandCluster($listings, $i, $neighbors, $clusterId, $epsMeters, $minPoints, $visited);
                $clusters[] = $cluster;
            }

            // Collect noise points
            $noise = [];
            foreach ($listings as $i => $point) {
                if (! isset($visited[$i])) {
                    $noise[] = $point;
                }
            }

            return [
                'clusters' => $clusters,
                'noise' => $noise,
                'stats' => [
                    'total_points' => count($listings),
                    'cluster_count' => $clusterId,
                    'noise_count' => count($noise),
                    'eps_km' => $epsKm,
                    'min_points' => $minPoints,
                ],
            ];
        });
    }

    private function regionQuery(array $points, int $index, float $epsMeters): array
    {
        $neighbors = [];
        $p = $points[$index];

        foreach ($points as $j => $other) {
            if ($i === $j) {
                continue;
            }

            $dist = $this->haversineDistance(
                $p['latitude'], $p['longitude'],
                $other['latitude'], $other['longitude']
            );

            if ($dist <= $epsMeters) {
                $neighbors[] = $j;
            }
        }

        return $neighbors;
    }

    private function expandCluster(array &$points, int $index, array $neighbors, int $clusterId, float $epsMeters, int $minPoints, array &$visited): array
    {
        $cluster = [$points[$index]];
        $visited[$index] = true;

        foreach ($neighbors as $neighborIndex) {
            if (isset($visited[$neighborIndex])) {
                continue;
            }
            $visited[$neighborIndex] = true;

            $newNeighbors = $this->regionQuery($points, $neighborIndex, $epsMeters);

            if (count($newNeighbors) >= $minPoints) {
                $neighbors = array_merge($neighbors, $newNeighbors);
            }
        }

        foreach ($neighbors as $neighborIndex) {
            if (! isset($visited[$neighborIndex])) {
                $visited[$neighborIndex] = true;
                $cluster[] = $points[$neighborIndex];
            }
        }

        return $cluster;
    }

    /**
     * Calculate commute/time distance (simplified - straight line)
     * In production: integrate with routing engine (OSRM, GraphHopper)
     */
    public function calculateCommute(float $fromLat, float $fromLon, float $toLat, float $toLon, string $mode = 'driving'): array
    {
        $distanceKm = $this->haversineDistance($fromLat, $fromLon, $toLat, $toLon) / 1000;

        // Rough time estimates by mode
        $speeds = [
            'walking' => 5,
            'cycling' => 15,
            'driving' => 40,
            'transit' => 25,
        ];

        $speed = $speeds[$mode] ?? 40;
        $timeMinutes = ($distanceKm / $speed) * 60;

        return [
            'distance_km' => round($distanceKm, 2),
            'estimated_time_minutes' => round($timeMinutes),
            'mode' => $mode,
            'note' => 'Straight-line estimate. Actual commute may vary.',
        ];
    }

    /**
     * Get nearby amenities within radius
     */
    public function getNearbyAmenities(float $latitude, float $longitude, float $radiusKm = 2): array
    {
        $amenityTypes = [
            'school' => ['primary', 'secondary', 'university'],
            'healthcare' => ['hospital', 'clinic', 'pharmacy'],
            'shopping' => ['mall', 'supermarket', 'market'],
            'transport' => ['bus_station', 'train_station', 'light_rail'],
            'recreation' => ['park', 'gym', 'stadium'],
            'finance' => ['bank', 'atm'],
            'dining' => ['restaurant', 'cafe'],
        ];

        $results = [];

        foreach ($amenityTypes as $category => $types) {
            // In production, query OSM/Overpass API or local amenities table
            // For now, return mock structure
            $results[$category] = [
                'count' => rand(0, 5),
                'nearest' => [
                    'name' => "Sample {$category}",
                    'distance_km' => round(rand(100, 2000) / 1000, 2),
                    'type' => $types[0],
                ],
            ];
        }

        return $results;
    }

    /**
     * Spatial join - get properties with neighborhood/admin boundaries
     */
    public function enrichWithBoundaries(Collection $properties): Collection
    {
        return $properties->map(function ($property) {
            if (! $property->latitude || ! $property->longitude) {
                return $property;
            }

            // Query PostGIS for containing boundaries
            $boundaries = DB::select('
                SELECT 
                    n.name_en as neighborhood,
                    n.name_am as neighborhood_am,
                    n.name_om as neighborhood_om,
                    k.name_en as kebele,
                    w.name_en as woreda,
                    sc.name_en as subcity,
                    c.name_en as city,
                    r.name_en as region
                FROM neighborhoods n
                LEFT JOIN kebeles k ON ST_Contains(k.geom, n.geom)
                LEFT JOIN woredas w ON ST_Contains(w.geom, k.geom)
                LEFT JOIN sub_cities sc ON ST_Contains(sc.geom, w.geom)
                LEFT JOIN cities c ON ST_Contains(c.geom, sc.geom)
                LEFT JOIN regions r ON ST_Contains(r.geom, c.geom)
                WHERE ST_Contains(n.geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))
                LIMIT 1
            ', [$property->longitude, $property->latitude]);

            if ($boundaries) {
                $property->boundaries = (array) $boundaries[0];
            }

            return $property;
        });
    }

    /**
     * Generate isochrone (reachable area within time)
     * Simplified - returns bounding box. Production: use pgRouting/OSRM
     */
    public function getIsochrone(float $latitude, float $longitude, int $minutes = 15, string $mode = 'driving'): array
    {
        $speeds = [
            'walking' => 5,
            'cycling' => 15,
            'driving' => 40,
            'transit' => 25,
        ];

        $speed = $speeds[$mode] ?? 40;
        $maxDistanceKm = ($speed * $minutes) / 60;

        // Approximate as circle
        $latDeg = $maxDistanceKm / 111.32;
        $lonDeg = $maxDistanceKm / (111.32 * cos(deg2rad($latitude)));

        return [
            'center' => ['lat' => $latitude, 'lon' => $longitude],
            'mode' => $mode,
            'minutes' => $minutes,
            'max_distance_km' => round($maxDistanceKm, 2),
            'bounds' => [
                'min_lat' => $latitude - $latDeg,
                'max_lat' => $latitude + $latDeg,
                'min_lon' => $longitude - $lonDeg,
                'max_lon' => $longitude + $lonDeg,
            ],
            'note' => 'Approximate circular isochrone. For accurate routing, use OSRM/GraphHopper.',
        ];
    }

    /**
     * Property boundary operations
     */
    public function setPropertyBoundary(Property $property, array $coordinates): bool
    {
        // Build polygon WKT
        $points = [];
        foreach ($coordinates as $coord) {
            $points[] = "{$coord[0]} {$coord[1]}";
        }
        $points[] = $coordinates[0][0].' '.$coordinates[0][1];
        $wkt = 'POLYGON(('.implode(', ', $points).'))';

        // Validate polygon (area > 0, valid geometry)
        $area = DB::selectOne('SELECT ST_Area(ST_GeomFromText(?, 4326)::geography) as area', [$wkt]);

        if (! $area || $area->area < 10) { // Less than 10 sq meters
            return false;
        }

        $property->update([
            'property_boundary' => $wkt,
            'property_point' => "POINT({$property->longitude} {$property->latitude})",
        ]);

        return true;
    }

    /**
     * Get properties intersecting a boundary (e.g., development project area)
     */
    public function getPropertiesIntersecting(string $boundaryWkt, array $filters = []): Collection
    {
        $query = ListingSearchIndex::query()
            ->where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw('
                ST_Intersects(
                    ST_SetSRID(ST_MakePoint(longitude, latitude), 4326),
                    ST_GeomFromText(?, 4326)
                )
            ', [$boundaryWkt]);

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Calculate area statistics for a boundary
     */
    public function getAreaStatistics(string $boundaryWkt): array
    {
        $stats = DB::selectOne("
            SELECT 
                COUNT(*) as listing_count,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price,
                AVG(building_area) as avg_area,
                COUNT(DISTINCT property_type_code) as type_count,
                COUNT(DISTINCT city) as city_count
            FROM listing_search_indices
            WHERE status = 'published'
            AND latitude IS NOT NULL
            AND longitude IS NOT NULL
            AND ST_Intersects(
                ST_SetSRID(ST_MakePoint(longitude, latitude), 4326),
                ST_GeomFromText(?, 4326)
            )
        ", [$boundaryWkt]);

        // Price distribution by type
        $byType = DB::select("
            SELECT 
                property_type_code,
                COUNT(*) as count,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price
            FROM listing_search_indices
            WHERE status = 'published'
            AND latitude IS NOT NULL
            AND longitude IS NOT NULL
            AND ST_Intersects(
                ST_SetSRID(ST_MakePoint(longitude, latitude), 4326),
                ST_GeomFromText(?, 4326)
            )
            GROUP BY property_type_code
        ", [$boundaryWkt]);

        return [
            'summary' => $stats ? (array) $stats : [],
            'by_type' => $byType,
            'boundary_area_sqm' => DB::selectOne('
                SELECT ST_Area(ST_GeomFromText(?, 4326)::geography) as area
            ', [$boundaryWkt])->area ?? 0,
        ];
    }

    /**
     * Apply common filters to GIS queries
     */
    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['purpose'])) {
            $query->where('purpose', $filters['purpose']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        if (! empty($filters['property_type'])) {
            $query->where('property_type_code', $filters['property_type']);
        }

        if (! empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }
    }

    /**
     * Haversine distance in meters
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
