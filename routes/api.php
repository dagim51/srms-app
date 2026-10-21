<?php

use App\Http\Controllers\Api\V1\AccommodationAvailabilityController;
use App\Http\Controllers\Api\V1\AccommodationBookingController;
use App\Http\Controllers\Api\V1\AccommodationController;
use App\Http\Controllers\Api\V1\AccommodationPricingRuleController;
use App\Http\Controllers\Api\V1\AIAssistantController;
use App\Http\Controllers\Api\V1\AmenityController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\CommunityController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\DevelopmentController;
use App\Http\Controllers\Api\V1\DisputeController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\FeatureController;
use App\Http\Controllers\Api\V1\FinancialsController;
use App\Http\Controllers\Api\V1\GISController;
use App\Http\Controllers\Api\V1\InquiryController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\ListingModerationController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\MarketingController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\OfferController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\ProfessionalServiceController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\PropertyManagementController;
use App\Http\Controllers\Api\V1\PropertyMediaController;
use App\Http\Controllers\Api\V1\PublicListingController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\RentalController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SearchAlertController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\ValuationController;
use App\Http\Controllers\Api\V1\ViewingController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Middleware\CheckPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is running cleanly.',
        'environment' => config('app.env'),
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('api.token');
        Route::post('/password/email', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
        Route::post('/password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1');
    });

    // Admin routes - require admin permission
    Route::prefix('admin')->middleware(['api.token', CheckPermission::class.':admin'])->group(function () {
        // Disputes & Fraud Management
        Route::get('/disputes', [DisputeController::class, 'index']);
        Route::get('/disputes/stats', [DisputeController::class, 'stats']);
        Route::get('/disputes/{dispute}', [DisputeController::class, 'show']);
        Route::post('/disputes/{dispute}/assign', [DisputeController::class, 'assign']);
        Route::patch('/disputes/{dispute}/status', [DisputeController::class, 'updateStatus']);
        Route::post('/disputes/{dispute}/evidence', [DisputeController::class, 'addEvidence']);
        Route::post('/evidence/{evidence}/verify', [DisputeController::class, 'verifyEvidence']);

        // Listing Flags
        Route::get('/flags/listings', [DisputeController::class, 'listingFlags']);
        Route::patch('/flags/listings/{flag}', [DisputeController::class, 'updateListingFlag']);

        // User Flags
        Route::get('/flags/users', [DisputeController::class, 'userFlags']);
        Route::patch('/flags/users/{flag}', [DisputeController::class, 'updateUserFlag']);
    });

    Route::middleware(['api.token', 'org.scope'])->group(function () {
        Route::name('api.v1.')->group(function () {
            Route::get('/users/profile', function (Request $request) {
                return response()->json(['data' => $request->user()]);
            });

            // Location Hierarchy
            Route::get('/locations/regions', [LocationController::class, 'regions']);
            Route::get('/locations/zones', [LocationController::class, 'zones']);
            Route::get('/locations/cities', [LocationController::class, 'cities']);
            Route::get('/locations/sub-cities', [LocationController::class, 'subCities']);
            Route::get('/locations/woredas', [LocationController::class, 'woredas']);
            Route::get('/locations/kebeles', [LocationController::class, 'kebeles']);

            // Administrative endpoints
            Route::post('/locations/regions', [LocationController::class, 'storeRegion'])
                ->middleware(CheckPermission::class.':manage_locations');

            // Property Core
            Route::get('/properties', [PropertyController::class, 'index']);
            Route::get('/properties/search/radius', [PropertyController::class, 'radiusSearch']);
            Route::post('/properties', [PropertyController::class, 'store']);
            Route::put('/properties/{property}', [PropertyController::class, 'update']);
            Route::post('/properties/{property}/amenities', [PropertyController::class, 'attachAmenity']);
            Route::delete('/properties/{property}/amenities/{amenityId}', [PropertyController::class, 'detachAmenity']);
            Route::post('/properties/{property}/features', [PropertyController::class, 'attachFeature']);

            // Property Media
            Route::post('/properties/{property}/media', [PropertyMediaController::class, 'store']);
            Route::put('/properties/{property}/media/order', [PropertyMediaController::class, 'updateOrder']);
            Route::delete('/properties/{property}/media/{media}', [PropertyMediaController::class, 'destroy']);

            // Listings
            Route::post('/properties/{property}/listings', [ListingController::class, 'store']);
            Route::put('/listings/{listing}', [ListingController::class, 'update']);
            Route::post('/listings/{listing}/submit', [ListingController::class, 'submit']);
            Route::patch('/listings/{listing}/status', [ListingController::class, 'updateStatus']);

            // Moderation
            Route::get('/moderation/listings', [ListingModerationController::class, 'index']);
            Route::post('/moderation/listings/{listing}/process', [ListingModerationController::class, 'process']);

            // Favorites
            Route::get('/favorites', [FavoriteController::class, 'index']);
            Route::post('/favorites', [FavoriteController::class, 'store']);
            Route::delete('/favorites/{property}', [FavoriteController::class, 'destroy']);

            // Collections
            Route::apiResource('collections', CollectionController::class)->except(['update']);
            Route::post('/collections/{collection}/properties', [CollectionController::class, 'addProperty']);
            Route::delete('/collections/{collection}/properties/{property}', [CollectionController::class, 'removeProperty']);
            Route::post('/collections/{collection}/reorder', [CollectionController::class, 'updateOrder']);

            // Inquiries & Messaging (Phase 16)
            Route::apiResource('inquiries', InquiryController::class)->except(['update']);
            Route::patch('/inquiries/{inquiry}/status', [InquiryController::class, 'update']);

            Route::apiResource('conversations', ConversationController::class)->only(['index', 'store', 'show', 'destroy']);
            Route::post('/messages', [MessageController::class, 'store']);
            Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

            // Property Viewing (Phase 17)
            Route::apiResource('viewings', ViewingController::class);

            // Notifications (Phase 18)
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead']);
            Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

            Route::get('/notification-preferences', [NotificationPreferenceController::class, 'show']);
            Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update']);

            // Reviews & Ratings (Phase 19/24)
            Route::get('/reviews', [ReviewController::class, 'index']);
            Route::post('/reviews', [ReviewController::class, 'store']);
            Route::post('/reviews/{review}/report', [ReviewController::class, 'report']);
            Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

            // Offers & Negotiation (Phase 18)
            Route::get('/offers', [OfferController::class, 'index']);
            Route::post('/offers', [OfferController::class, 'store']);
            Route::patch('/offers/{offer}', [OfferController::class, 'update']);

            // Marketplace Reservations (Phase 19)
            Route::get('/reservations', [ReservationController::class, 'index']);
            Route::post('/reservations', [ReservationController::class, 'store']);
            Route::patch('/reservations/{reservation}', [ReservationController::class, 'update']);

            // Accommodation & Host Model (Phase 20)
            Route::apiResource('accommodations', AccommodationController::class);

            // Availability & Calendar (Phase 21)
            Route::get('/accommodation-availabilities', [AccommodationAvailabilityController::class, 'index']);
            Route::post('/accommodation-availabilities', [AccommodationAvailabilityController::class, 'store']);
            Route::delete('/accommodation-availabilities/{accommodationAvailability}', [AccommodationAvailabilityController::class, 'destroy']);

            // Pricing & Yield Management (Phase 22)
            Route::get('/accommodation-pricing-rules', [AccommodationPricingRuleController::class, 'index']);
            Route::post('/accommodation-pricing-rules', [AccommodationPricingRuleController::class, 'store']);
            Route::delete('/accommodation-pricing-rules/{accommodationPricingRule}', [AccommodationPricingRuleController::class, 'destroy']);

            // Booking Engine (Phase 23)
            Route::get('/accommodation-bookings', [AccommodationBookingController::class, 'index']);
            Route::post('/accommodation-bookings', [AccommodationBookingController::class, 'store'])->middleware('idempotency');
            Route::patch('/accommodation-bookings/{accommodationBooking}', [AccommodationBookingController::class, 'update']);

            // Payments & Transactions (Phase 24)
            Route::get('/payments', [PaymentController::class, 'index']);
            Route::post('/payments', [PaymentController::class, 'store'])->name('payments')->middleware('idempotency');
            Route::patch('/payments/{payment}', [PaymentController::class, 'update']);

            // Companies & Organizations (Phase 26)
            Route::get('/organizations', [OrganizationController::class, 'index']);
            Route::post('/organizations', [OrganizationController::class, 'store']);
            Route::post('/organizations/{organization}/members', [OrganizationController::class, 'addMember']);

            // CRM (Phase 27)
            Route::get('/leads', [LeadController::class, 'index']);
            Route::post('/leads', [LeadController::class, 'store']);
            Route::patch('/leads/{lead}', [LeadController::class, 'update']);
            Route::post('/leads/{lead}/activities', [LeadController::class, 'addActivity']);

            // Development (Phase 28)
            Route::get('/development/projects', [DevelopmentController::class, 'index']);
            Route::post('/development/projects', [DevelopmentController::class, 'storeProject']);
            Route::post('/development/projects/{project}/buildings', [DevelopmentController::class, 'storeBuilding']);
            Route::post('/development/buildings/{building}/units', [DevelopmentController::class, 'storeUnit']);
            Route::post('/development/projects/{project}/payment-plans', [DevelopmentController::class, 'storePaymentPlan']);

            // Rental (Phase 29)
            Route::post('/rental/profile', [RentalController::class, 'storeTenantProfile']);
            Route::post('/rental/applications', [RentalController::class, 'storeApplication']);
            Route::post('/rental/applications/{application}/approve', [RentalController::class, 'approveApplication']);

            // Property Management (Phase 30)
            Route::post('/pm/tickets', [PropertyManagementController::class, 'storeTicket']);
            Route::patch('/pm/tickets/{ticket}/status', [PropertyManagementController::class, 'updateTicketStatus']);
            Route::post('/pm/cleaning-tasks', [PropertyManagementController::class, 'storeCleaningTask']);
            Route::patch('/pm/cleaning-tasks/{task}/status', [PropertyManagementController::class, 'updateCleaningStatus']);

            // Community (Phase 31)
            Route::post('/communities', [CommunityController::class, 'storeCommunity']);
            Route::post('/communities/{community}/facilities', [CommunityController::class, 'storeFacility']);
            Route::post('/facilities/{facility}/bookings', [CommunityController::class, 'storeBooking']);

            // Documents & Contracts (Phase 32)
            Route::post('/documents/templates', [DocumentController::class, 'storeTemplate']);
            Route::post('/documents/contracts', [DocumentController::class, 'generateContract']);
            Route::post('/documents/contracts/{contract}/sign', [DocumentController::class, 'signContract']);

            // Financials & Ledger (Phase 33)
            Route::post('/financials/accounts', [FinancialsController::class, 'storeAccount']);
            Route::post('/financials/ledger-entries', [FinancialsController::class, 'recordDoubleEntry']);
            Route::post('/financials/invoices', [FinancialsController::class, 'storeInvoice']);

            // Reports & Analytics (Phase 34)
            Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
            Route::post('/analytics/templates', [AnalyticsController::class, 'createTemplate']);
            Route::post('/analytics/reports', [AnalyticsController::class, 'generateReport']);

            // Marketing & Leads (Phase 35)
            Route::post('/marketing/campaigns', [MarketingController::class, 'storeCampaign']);
            Route::post('/marketing/leads', [MarketingController::class, 'storeLead']);
            Route::post('/marketing/leads/{lead}/convert', [MarketingController::class, 'convertLead']);

            // Valuation & Investment (Phase 41)
            Route::post('/properties/{property}/valuation', [ValuationController::class, 'calculate']);
            Route::get('/properties/{property}/valuation', [ValuationController::class, 'index']);

            // Professional Services (Phase 42)
            Route::post('/services/profiles', [ProfessionalServiceController::class, 'storeProfile']);
            Route::post('/services/profiles/{profile}/listings', [ProfessionalServiceController::class, 'storeListing']);
            Route::post('/services/listings/{listing}/requests', [ProfessionalServiceController::class, 'storeRequest']);
            Route::patch('/services/requests/{serviceRequest}/status', [ProfessionalServiceController::class, 'updateRequestStatus']);

            // Search Alerts & Recommendations (Phase 37)
            Route::post('/search/alerts', [SearchAlertController::class, 'store']);

            // Recommendation Engine (Phase 37)
            Route::get('/recommendations/for-user', [RecommendationController::class, 'forUser']);
            Route::get('/recommendations/similar/{listingId}', [RecommendationController::class, 'similar']);
            Route::get('/recommendations/trending', [RecommendationController::class, 'trending']);
            Route::get('/recommendations/because-you-viewed', [RecommendationController::class, 'becauseYouViewed']);
            Route::get('/recommendations/collaborative', [RecommendationController::class, 'collaborative']);

            // AI Assistant (Phase 38)
            Route::post('/ai/description', [AIAssistantController::class, 'generateDescription']);
            Route::post('/ai/title', [AIAssistantController::class, 'generateTitle']);
            Route::post('/ai/improve-description', [AIAssistantController::class, 'improveDescription']);
            Route::post('/ai/marketing-copy', [AIAssistantController::class, 'marketingCopy']);
            Route::post('/ai/chat', [AIAssistantController::class, 'chat']);
            Route::post('/ai/extract', [AIAssistantController::class, 'extractPropertyData']);
            Route::post('/ai/market-analysis', [AIAssistantController::class, 'analyzeMarket']);
            Route::post('/ai/contract-clauses', [AIAssistantController::class, 'contractClauses']);

            // Advanced GIS Operations (Phase 39)
            Route::post('/gis/radius-search', [GISController::class, 'radiusSearch']);
            Route::post('/gis/polygon-search', [GISController::class, 'polygonSearch']);
            Route::post('/gis/nearest', [GISController::class, 'nearest']);
            Route::post('/gis/heatmap', [GISController::class, 'heatmap']);
            Route::post('/gis/clusters', [GISController::class, 'clusters']);
            Route::post('/gis/commute', [GISController::class, 'commute']);
            Route::post('/gis/amenities', [GISController::class, 'amenities']);
            Route::post('/properties/{property}/boundary', [GISController::class, 'setBoundary']);
            Route::post('/gis/intersecting', [GISController::class, 'intersecting']);
            Route::post('/gis/area-statistics', [GISController::class, 'areaStatistics']);
            Route::post('/gis/isochrone', [GISController::class, 'isochrone']);
            Route::post('/gis/commute-between', [GISController::class, 'commuteBetween']);

            // Disputes & Fraud Reporting (Phase 34 - User-facing)
            Route::apiResource('disputes', DisputeController::class)->only(['index', 'store', 'show']);
            Route::post('/disputes/{dispute}/evidence', [DisputeController::class, 'addEvidence']);
            Route::post('/flags/listings', [DisputeController::class, 'flagListing']);
            Route::post('/flags/users', [DisputeController::class, 'flagUser']);
            Route::get('/my-flags/listings', [DisputeController::class, 'myListingFlags']);
            Route::get('/my-flags/users', [DisputeController::class, 'myUserFlags']);
        });
    });
});

// Public Routes
Route::post('/webhooks/{provider}', [WebhookController::class, 'handle']);
Route::post('/webhooks/payments/telebirr', [PaymentWebhookController::class, 'telebirr'])
    ->middleware('throttle:webhooks');

Route::prefix('v1')->group(function () {
    Route::get('/public/listings', [PublicListingController::class, 'index']);
    Route::get('/public/listings/{listing}', [PublicListingController::class, 'show']);

    Route::get('/search', [SearchController::class, 'search']);

    // Property Catalogs (Phase 08)
    Route::get('/amenities', [AmenityController::class, 'index']);
    Route::get('/amenities/categories', [AmenityController::class, 'categories']);
    Route::get('/features', [FeatureController::class, 'index']);

    // GIS Foundation (Phase 10)
    Route::get('/properties/search/radius', [PropertyController::class, 'radiusSearch']);
});
