<?php

namespace App\Services\Valuation;

use App\Models\ListingSearchIndex;
use App\Models\Property;
use Illuminate\Support\Collection;

class ValuationService
{
    /**
     * Calculate comprehensive property valuation
     */
    public function calculate(Property $property, array $inputs = []): array
    {
        $methods = [];

        // Method 1: Comparable Sales (Market Approach)
        $comparables = $this->findComparables($property);
        if ($comparables->count() >= 3) {
            $methods['comparables'] = $this->calculateFromComparables($property, $comparables);
        }

        // Method 2: Hedonic Regression Model
        if ($property->bedrooms && $property->building_area) {
            $methods['hedonic'] = $this->calculateHedonic($property);
        }

        // Method 3: Income Approach (for rental/commercial)
        if ($property->listings()->where('purpose', 'For Rent')->exists()) {
            $methods['income'] = $this->calculateIncomeApproach($property);
        }

        // Method 4: Cost Approach (for new construction)
        if ($property->construction_year && $property->construction_year >= now()->year - 5) {
            $methods['cost'] = $this->calculateCostApproach($property);
        }

        // Method 5: User-provided financial inputs (from API)
        if (! empty($inputs)) {
            $methods['user_inputs'] = $this->calculateFromUserInputs($property, $inputs);
        }

        // Ensemble: weighted average of available methods
        $ensemble = $this->ensembleValuation($methods);

        return [
            'property_id' => $property->id,
            'methods' => $methods,
            'ensemble_value' => round($ensemble['value']),
            'confidence' => $ensemble['confidence'],
            'range' => [
                'low' => round($ensemble['value'] * 0.9),
                'high' => round($ensemble['value'] * 1.1),
            ],
            'breakdown' => $ensemble['breakdown'],
            'comparables_used' => $comparables->count() ?? 0,
            'calculated_at' => now()->toISOString(),
        ];
    }

    /**
     * Find comparable properties using PostGIS spatial query + attribute matching
     */
    public function findComparables(Property $property, int $limit = 10, float $radiusKm = 5): Collection
    {
        // Use the model's scopeWithinRadius for PostGIS
        $candidates = ListingSearchIndex::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('price')
            ->where('price', '>', 0);

        if ($firstListing = $property->listings->first()) {
            $candidates->where('listing_id', '!=', $firstListing->id);
        }

        // Spatial filter
        if ($property->latitude && $property->longitude) {
            $candidates->whereRaw('
                ST_DWithin(
                    ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography,
                    ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                    ?
                )
            ', [$property->longitude, $property->latitude, $radiusKm * 1000]);
        }

        // Attribute similarity filters
        if ($property->bedrooms) {
            $candidates->whereBetween('bedrooms', [
                max(0, $property->bedrooms - 1),
                $property->bedrooms + 1,
            ]);
        }

        if ($property->property_type_code) {
            $candidates->where('property_type_code', $property->property_type_code);
        }

        if ($property->purpose) {
            $candidates->where('purpose', $property->purpose);
        }

        // Recent transactions preferred (last 12 months)
        $candidates->where('published_at', '>=', now()->subYear());

        return $candidates->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate value from comparable sales with adjustments
     */
    private function calculateFromComparables(Property $subject, Collection $comparables): array
    {
        $adjustedValues = [];

        foreach ($comparables as $comp) {
            $adjustment = 1.0;
            $notes = [];

            // Adjust for bedroom difference
            $bedDiff = ($subject->bedrooms ?? 0) - ($comp->bedrooms ?? 0);
            if ($bedDiff !== 0) {
                $adjustment += $bedDiff * 0.05; // 5% per bedroom
                $prefix = $bedDiff > 0 ? '+' : '';
                $notes[] = "Bedrooms: {$prefix}{$bedDiff}";
            }

            // Adjust for area difference
            if ($subject->building_area && $comp->building_area) {
                $areaDiff = (($subject->building_area - $comp->building_area) / $comp->building_area);
                $adjustment += $areaDiff * 0.8; // 80% of area diff
                $notes[] = 'Area: '.($areaDiff > 0 ? '+' : '').round($areaDiff * 100, 1).'%';
            }

            // Adjust for age difference
            if ($subject->construction_year && $comp->construction_year) {
                $ageDiff = ($subject->construction_year - $comp->construction_year);
                if (abs($ageDiff) > 5) {
                    $adjustment -= $ageDiff * 0.005; // 0.5% per year
                    $notes[] = "Age: {$ageDiff} years";
                }
            }

            // Adjust for location quality (simplified: same city = no adj)
            if ($subject->city !== $comp->city) {
                $adjustment *= 0.95; // 5% discount for different area
                $notes[] = 'Different city';
            }

            $adjustedPrice = $comp->price * $adjustment;
            $adjustedValues[] = [
                'comparable_id' => $comp->id,
                'original_price' => $comp->price,
                'adjusted_price' => round($adjustedPrice),
                'adjustment_factor' => round($adjustment, 3),
                'notes' => $notes,
            ];
        }

        // Weight by recency and similarity
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($adjustedValues as $i => $av) {
            // More recent = higher weight
            $recencyWeight = 1 / (1 + $i * 0.1);
            $weightedSum += $av['adjusted_price'] * $recencyWeight;
            $totalWeight += $recencyWeight;
        }

        $value = $totalWeight > 0 ? $weightedSum / $totalWeight : 0;

        return [
            'method' => 'comparable_sales',
            'value' => round($value),
            'confidence' => min(90, 50 + $comparables->count() * 8), // 50-90%
            'details' => $adjustedValues,
        ];
    }

    /**
     * Hedonic regression model for Ethiopian market
     * Coefficients calibrated for Addis Ababa market (sample values)
     */
    private function calculateHedonic(Property $property): array
    {
        // Base price per m² by city (ETB) - sample coefficients
        $basePricePerSqm = match ($property->city) {
            'Addis Ababa' => 45000,
            'Bole' => 55000,
            'Kazanchis' => 60000,
            'Bole Atlas' => 65000,
            'Old Airport' => 50000,
            'CMC Michael' => 40000,
            'Hawassa' => 25000,
            'Bahir Dar' => 30000,
            'Mekelle' => 28000,
            'Dire Dawa' => 22000,
            default => 20000,
        };

        // Property type multiplier
        $typeMultiplier = match ($property->property_type_code) {
            'villa' => 1.4,
            'apartment' => 1.0,
            'townhouse' => 1.2,
            'penthouse' => 1.6,
            'duplex' => 1.3,
            'studio' => 0.8,
            default => 1.0,
        };

        // Bedroom coefficient
        $bedCoeff = ($property->bedrooms ?? 0) * 0.08; // 8% per bedroom

        // Bathroom coefficient
        $bathCoeff = ($property->bathrooms ?? 0) * 0.05;

        // Building area (primary driver)
        $area = $property->building_area ?? 100;
        $baseValue = $area * $basePricePerSqm * $typeMultiplier;

        // Feature adjustments
        $featureAdj = 1.0;
        if ($property->hasFeature('parking')) {
            $featureAdj *= 1.05;
        }
        if ($property->hasFeature('garden')) {
            $featureAdj *= 1.03;
        }
        if ($property->hasFeature('pool')) {
            $featureAdj *= 1.15;
        }
        if ($property->hasFeature('gym')) {
            $featureAdj *= 1.08;
        }
        if ($property->hasFeature('security')) {
            $featureAdj *= 1.04;
        }

        // Age depreciation
        $ageAdj = 1.0;
        if ($property->construction_year) {
            $age = now()->year - $property->construction_year;
            $ageAdj = max(0.5, 1 - ($age * 0.01)); // 1% per year, floor at 50%
        }

        // Condition adjustment
        $conditionAdj = match ($property->condition) {
            'excellent' => 1.1,
            'good' => 1.0,
            'fair' => 0.9,
            'poor' => 0.75,
            default => 1.0,
        };

        $value = $baseValue * (1 + $bedCoeff) * (1 + $bathCoeff) * $featureAdj * $ageAdj * $conditionAdj;

        return [
            'method' => 'hedonic_regression',
            'value' => round($value),
            'confidence' => 75,
            'details' => [
                'base_price_per_sqm' => $basePricePerSqm,
                'area' => $area,
                'type_multiplier' => $typeMultiplier,
                'bed_coeff' => $bedCoeff,
                'bath_coeff' => $bathCoeff,
                'feature_adj' => $featureAdj,
                'age_adj' => $ageAdj,
                'condition_adj' => $conditionAdj,
            ],
        ];
    }

    /**
     * Income approach for rental properties
     */
    private function calculateIncomeApproach(Property $property): array
    {
        $rentalListing = $property->listings()->where('purpose', 'For Rent')->first();

        if (! $rentalListing || ! $rentalListing->price) {
            return ['method' => 'income', 'value' => 0, 'confidence' => 0];
        }

        $monthlyRent = $rentalListing->price;
        $annualRent = $monthlyRent * 12;

        // Estimate operating expenses (30% of gross rent for Ethiopia)
        $operatingExpenses = $annualRent * 0.30;
        $noi = $annualRent - $operatingExpenses;

        // Market cap rate by city
        $capRate = match ($property->city) {
            'Addis Ababa' => 0.07,    // 7%
            'Bole' => 0.065,
            'Kazanchis' => 0.06,
            'Hawassa' => 0.085,
            'Bahir Dar' => 0.08,
            default => 0.09,
        };

        $value = $capRate > 0 ? $noi / $capRate : 0;

        // Gross Rent Multiplier (GRM) cross-check
        $grm = match ($property->city) {
            'Addis Ababa' => 12,
            'Bole' => 14,
            default => 10,
        };
        $grmValue = $annualRent * $grm;

        // Average of both methods
        $avgValue = ($value + $grmValue) / 2;

        return [
            'method' => 'income_approach',
            'value' => round($avgValue),
            'confidence' => 70,
            'details' => [
                'monthly_rent' => $monthlyRent,
                'annual_rent' => $annualRent,
                'noi' => round($noi),
                'cap_rate' => $capRate,
                'grm' => $grm,
                'grm_value' => round($grmValue),
                'cap_value' => round($value),
            ],
        ];
    }

    /**
     * Cost approach for new construction
     */
    private function calculateCostApproach(Property $property): array
    {
        if (! $property->building_area || ! $property->construction_year) {
            return ['method' => 'cost', 'value' => 0, 'confidence' => 0];
        }

        // Construction cost per m² by quality
        $costPerSqm = match ($property->condition) {
            'excellent' => 65000,
            'good' => 55000,
            'fair' => 45000,
            'poor' => 35000,
            default => 50000,
        };

        // Land value estimation (30% of total for urban)
        $constructionCost = $property->building_area * $costPerSqm;

        // Depreciation
        $age = now()->year - $property->construction_year;
        $economicLife = 50; // years
        $depreciation = min(0.8, $age / $economicLife);
        $depreciatedCost = $constructionCost * (1 - $depreciation);

        // Land value (estimated as 30% of total)
        $totalCost = $depreciatedCost / 0.7;

        return [
            'method' => 'cost_approach',
            'value' => round($totalCost),
            'confidence' => 60,
            'details' => [
                'construction_cost_per_sqm' => $costPerSqm,
                'building_area' => $property->building_area,
                'construction_cost' => round($constructionCost),
                'depreciation' => round($depreciation * 100, 1).'%',
                'depreciated_cost' => round($depreciatedCost),
                'land_value_estimate' => round($totalCost * 0.3),
            ],
        ];
    }

    /**
     * Calculate from user-provided financial inputs
     */
    private function calculateFromUserInputs(Property $property, array $inputs): array
    {
        $purchasePrice = $inputs['purchase_price'] ?? 0;
        $monthlyRent = $inputs['monthly_rent'] ?? 0;
        $operatingExpenses = $inputs['operating_expenses_yearly'] ?? 0;
        $downPayment = $inputs['down_payment'] ?? $purchasePrice;

        $annualRent = $monthlyRent * 12;
        $noi = $annualRent - $operatingExpenses;

        $capRate = $purchasePrice > 0 ? $noi / $purchasePrice : 0;
        $cashOnCash = $downPayment > 0 ? $noi / $downPayment : 0;

        return [
            'method' => 'user_inputs',
            'value' => $purchasePrice,
            'confidence' => 50,
            'details' => [
                'cap_rate' => round($capRate * 100, 2).'%',
                'cash_on_cash' => round($cashOnCash * 100, 2).'%',
                'noi' => $noi,
                'dscr' => $noi > 0 ? 1.25 : 0, // Simplified
            ],
        ];
    }

    /**
     * Ensemble valuation combining all methods
     */
    private function ensembleValuation(array $methods): array
    {
        if (empty($methods)) {
            return ['value' => 0, 'confidence' => 0, 'breakdown' => []];
        }

        // Weights by method reliability
        $weights = [
            'comparables' => 0.40,      // Market approach - most reliable
            'hedonic' => 0.25,          // Model-based
            'income' => 0.20,           // For rental properties
            'cost' => 0.10,             // New construction only
            'user_inputs' => 0.05,      // User-provided (least weight)
        ];

        $totalWeight = 0;
        $weightedSum = 0;
        $breakdown = [];

        foreach ($methods as $key => $method) {
            $weight = $weights[$key] ?? 0;
            if ($weight === 0 || ! isset($method['value']) || $method['value'] <= 0) {
                continue;
            }

            $confidence = $method['confidence'] ?? 50;
            $effectiveWeight = $weight * ($confidence / 100);

            $weightedSum += $method['value'] * $effectiveWeight;
            $totalWeight += $effectiveWeight;

            $breakdown[] = [
                'method' => $key,
                'value' => $method['value'],
                'weight' => $weight,
                'confidence' => $confidence,
                'contribution' => round($method['value'] * $effectiveWeight / max($totalWeight, 0.001)),
            ];
        }

        $ensembleValue = $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
        $avgConfidence = $methods ? array_sum(array_column($methods, 'confidence')) / count($methods) : 0;

        return [
            'value' => $ensembleValue,
            'confidence' => round($avgConfidence),
            'breakdown' => $breakdown,
            'is_authoritative' => false,
        ];
    }

    /**
     * Get market trends for area
     */
    public function getMarketTrends(string $city, ?string $subcity = null): array
    {
        $query = ListingSearchIndex::query();

        $query->where(function ($q) use ($city, $subcity) {
            $q->where('title', 'like', "%{$city}%")
                ->orWhere('description', 'like', "%{$city}%");
            if ($subcity) {
                $q->orWhere('title', 'like', "%{$subcity}%")
                    ->orWhere('description', 'like', "%{$subcity}%");
            }
        });

        $listings = $query->where('published_at', '>=', now()->subMonths(12))
            ->get();

        if ($listings->isEmpty()) {
            return ['message' => 'Insufficient data'];
        }

        $avgPrice = $listings->avg('price');
        $medianPrice = $this->median($listings->pluck('price')->toArray());
        $pricePerSqm = $avgPrice;

        // Monthly trend
        $monthly = $listings->groupBy(fn ($l) => $l->published_at->format('Y-m'))
            ->map(fn ($group) => [
                'count' => $group->count(),
                'avg_price' => $group->avg('price'),
                'median_price' => $this->median($group->pluck('price')->toArray()),
            ]);

        return [
            'city' => $city,
            'subcity' => $subcity,
            'period' => '12 months',
            'total_transactions' => $listings->count(),
            'avg_price' => round($avgPrice),
            'median_price' => round($medianPrice),
            'avg_price_per_sqm' => round($pricePerSqm ?? 0),
            'monthly_trend' => $monthly,
            'price_change_yoy' => $this->calculateYoYChange($listings),
        ];
    }

    private function median(array $values): float
    {
        if (empty($values)) {
            return 0;
        }
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        return $count % 2 ? $values[$middle] : (($values[$middle - 1] + $values[$middle]) / 2);
    }

    private function calculateYoYChange(Collection $listings): float
    {
        $thisYear = $listings->where('published_at', '>=', now()->startOfYear())->avg('price');
        $lastYear = $listings->where('published_at', '<', now()->startOfYear())
            ->where('published_at', '>=', now()->subYear()->startOfYear())->avg('price');

        if (! $lastYear || $lastYear == 0) {
            return 0;
        }

        return round((($thisYear - $lastYear) / $lastYear) * 100, 1);
    }
}
