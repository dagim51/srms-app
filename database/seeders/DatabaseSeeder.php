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
     * 2. LocationSeeder - Country/Region/City hierarchy (no dependencies)
     * 3. AmenityAndFeatureSeeder - Amenities/Features (no dependencies)
     * 4. PublicDiscoverySeeder - Properties + Listings (needs Location, Amenities)
     * 5. PropertyMediaSeeder - Additional media (needs Properties)
     * 6. PropertyGisSeeder - GIS properties + listings (needs Location)
     * 7. SearchEngineCatalogSeeder - Categories, Types, Verification (needs Properties, Amenities)
     * 8. ModerationUserSeeder - Admin/Seller users + moderation states (needs Roles, Listings)
     *
     * DemoDataSeeder (optional, via --class=DemoDataSeeder): richer demo data
     */
    public function run(): void
    {
        // Core authorization - must be first
        $this->call(AuthorizationSeeder::class);

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

        // Moderation users + listing states (published, pending, rejected)
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