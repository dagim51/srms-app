<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order matters: dependencies must be seeded first.
     * 1. AuthorizationSeeder - Roles/Permissions (no dependencies)
     * 2. DemoUserSeeder - All demo users for all domains (needs Roles)
     * 3. LocationSeeder - Country/Region/City hierarchy (no dependencies)
     * 4. AmenityAndFeatureSeeder - Amenities/Features (no dependencies)
     * 5. PublicDiscoverySeeder - Properties + Listings (needs Location, Amenities, Users)
     * 6. PropertyMediaSeeder - Additional media (needs Properties)
     * 7. PropertyGisSeeder - GIS properties + listings (needs Location)
     * 8. SearchEngineCatalogSeeder - Categories, Types, Verification (needs Properties, Amenities)
     * 9. ModerationUserSeeder - Moderation states on existing listings (needs Roles, Listings, Users)
     *
     * Advanced Verticals: Rental, Developer Sales, Operations, Finance, Trust
     * (These run after core domain seeders and can reference seeded users)
     */
    public function run(): void
    {
        // Core authorization - must be first
        $this->call(AuthorizationSeeder::class);

        // Demo users for ALL domains - must run before domain seeders
        $this->call(DemoUserSeeder::class);

        // Location hierarchy - needed by properties
        $this->call(LocationSeeder::class);

        // Catalog data - amenities, features, categories, types
        $this->call(AmenityAndFeatureSeeder::class);

        // Properties & listings for public discovery (homepage, search)
        $this->call(PublicDiscoverySeeder::class);

        // Additional property media
        $this->call(PropertyMediaSeeder::class);

        // GIS properties with spatial data
        $this->call(PropertyGisSeeder::class);

        // Search engine catalog (categories, types, verification, amenities linking)
        $this->call(SearchEngineCatalogSeeder::class);

        // Moderation states on existing listings
        $this->call(ModerationUserSeeder::class);

        // Advanced Verticals: Rental, Developer Sales, Operations, Finance, Trust
        $this->call(RentalSeeder::class);
        $this->call(DeveloperSalesSeeder::class);
        $this->call(OperationsSeeder::class);
        $this->call(FinancialLedgerSeeder::class);
        $this->call(TrustSafetySeeder::class);

        $this->command?->info('Core database seeded successfully with advanced operational domains.');
    }
}
