<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\AuthController;

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
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('api.token');
        Route::post('/password/email', [AuthController::class, 'forgotPassword']);
        Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('api.token')->group(function () {
        Route::get('/users/profile', function (Request $request) {
            return response()->json(['data' => $request->user()]);
        });

        // Location Hierarchy
        Route::get('/locations/regions', [\App\Http\Controllers\Api\V1\LocationController::class, 'regions']);
        Route::get('/locations/zones', [\App\Http\Controllers\Api\V1\LocationController::class, 'zones']);
        Route::get('/locations/cities', [\App\Http\Controllers\Api\V1\LocationController::class, 'cities']);
        Route::get('/locations/sub-cities', [\App\Http\Controllers\Api\V1\LocationController::class, 'subCities']);
        Route::get('/locations/woredas', [\App\Http\Controllers\Api\V1\LocationController::class, 'woredas']);
        Route::get('/locations/kebeles', [\App\Http\Controllers\Api\V1\LocationController::class, 'kebeles']);
        
        // Administrative endpoints
        Route::post('/locations/regions', [\App\Http\Controllers\Api\V1\LocationController::class, 'storeRegion'])
            ->middleware(\App\Http\Middleware\CheckPermission::class.':manage_locations');

        // Property Core
        Route::get('/properties', [\App\Http\Controllers\Api\V1\PropertyController::class, 'index']);
        Route::get('/properties/search/radius', [\App\Http\Controllers\Api\V1\PropertyController::class, 'radiusSearch']);
        Route::post('/properties', [\App\Http\Controllers\Api\V1\PropertyController::class, 'store']);
        Route::put('/properties/{property}', [\App\Http\Controllers\Api\V1\PropertyController::class, 'update']);
        Route::post('/properties/{property}/amenities', [\App\Http\Controllers\Api\V1\PropertyController::class, 'attachAmenity']);
        Route::delete('/properties/{property}/amenities/{amenityId}', [\App\Http\Controllers\Api\V1\PropertyController::class, 'detachAmenity']);
        Route::post('/properties/{property}/features', [\App\Http\Controllers\Api\V1\PropertyController::class, 'attachFeature']);

        // Property Media
        Route::post('/properties/{property}/media', [\App\Http\Controllers\Api\V1\PropertyMediaController::class, 'store']);
        Route::put('/properties/{property}/media/order', [\App\Http\Controllers\Api\V1\PropertyMediaController::class, 'updateOrder']);
        Route::delete('/properties/{property}/media/{media}', [\App\Http\Controllers\Api\V1\PropertyMediaController::class, 'destroy']);

        // Listings
        Route::post('/properties/{property}/listings', [\App\Http\Controllers\Api\V1\ListingController::class, 'store']);
        Route::put('/listings/{listing}', [\App\Http\Controllers\Api\V1\ListingController::class, 'update']);
        Route::post('/listings/{listing}/submit', [\App\Http\Controllers\Api\V1\ListingController::class, 'submit']);
        Route::patch('/listings/{listing}/status', [\App\Http\Controllers\Api\V1\ListingController::class, 'updateStatus']);

        // Moderation
        Route::get('/moderation/listings', [\App\Http\Controllers\Api\V1\ListingModerationController::class, 'index']);
        Route::post('/moderation/listings/{listing}/process', [\App\Http\Controllers\Api\V1\ListingModerationController::class, 'process']);
        
        // Favorites
        Route::get('/favorites', [\App\Http\Controllers\Api\V1\FavoriteController::class, 'index']);
        Route::post('/favorites', [\App\Http\Controllers\Api\V1\FavoriteController::class, 'store']);
        Route::delete('/favorites/{property}', [\App\Http\Controllers\Api\V1\FavoriteController::class, 'destroy']);
        
        // Collections
        Route::apiResource('collections', \App\Http\Controllers\Api\V1\CollectionController::class)->except(['update']);
        Route::post('/collections/{collection}/properties', [\App\Http\Controllers\Api\V1\CollectionController::class, 'addProperty']);
        Route::delete('/collections/{collection}/properties/{property}', [\App\Http\Controllers\Api\V1\CollectionController::class, 'removeProperty']);
        Route::post('/collections/{collection}/reorder', [\App\Http\Controllers\Api\V1\CollectionController::class, 'updateOrder']);
        
        // Inquiries & Messaging (Phase 16)
        Route::apiResource('inquiries', \App\Http\Controllers\Api\V1\InquiryController::class)->except(['update']);
        Route::patch('/inquiries/{inquiry}/status', [\App\Http\Controllers\Api\V1\InquiryController::class, 'update']);
        
        Route::apiResource('conversations', \App\Http\Controllers\Api\V1\ConversationController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::post('/messages', [\App\Http\Controllers\Api\V1\MessageController::class, 'store']);
        Route::delete('/messages/{message}', [\App\Http\Controllers\Api\V1\MessageController::class, 'destroy']);
        
        // Property Viewing (Phase 17)
        Route::apiResource('viewings', \App\Http\Controllers\Api\V1\ViewingController::class);
        
        // Notifications (Phase 18)
        Route::get('/notifications', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
        Route::post('/notifications/mark-read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\V1\NotificationController::class, 'destroy']);
        
        Route::get('/notification-preferences', [\App\Http\Controllers\Api\V1\NotificationPreferenceController::class, 'show']);
        Route::put('/notification-preferences', [\App\Http\Controllers\Api\V1\NotificationPreferenceController::class, 'update']);
        
        // Reviews & Ratings (Phase 19/24)
        Route::get('/reviews', [\App\Http\Controllers\Api\V1\ReviewController::class, 'index']);
        Route::post('/reviews', [\App\Http\Controllers\Api\V1\ReviewController::class, 'store']);
        Route::post('/reviews/{review}/report', [\App\Http\Controllers\Api\V1\ReviewController::class, 'report']);
        Route::delete('/reviews/{review}', [\App\Http\Controllers\Api\V1\ReviewController::class, 'destroy']);
        
        // Offers & Negotiation (Phase 18)
        Route::get('/offers', [\App\Http\Controllers\Api\V1\OfferController::class, 'index']);
        Route::post('/offers', [\App\Http\Controllers\Api\V1\OfferController::class, 'store']);
        Route::patch('/offers/{offer}', [\App\Http\Controllers\Api\V1\OfferController::class, 'update']);
        
        // Marketplace Reservations (Phase 19)
        Route::get('/reservations', [\App\Http\Controllers\Api\V1\ReservationController::class, 'index']);
        Route::post('/reservations', [\App\Http\Controllers\Api\V1\ReservationController::class, 'store']);
        Route::patch('/reservations/{reservation}', [\App\Http\Controllers\Api\V1\ReservationController::class, 'update']);
        
        // Accommodation & Host Model (Phase 20)
        Route::apiResource('accommodations', \App\Http\Controllers\Api\V1\AccommodationController::class);
        
        // Availability & Calendar (Phase 21)
        Route::get('/accommodation-availabilities', [\App\Http\Controllers\Api\V1\AccommodationAvailabilityController::class, 'index']);
        Route::post('/accommodation-availabilities', [\App\Http\Controllers\Api\V1\AccommodationAvailabilityController::class, 'store']);
        Route::delete('/accommodation-availabilities/{accommodationAvailability}', [\App\Http\Controllers\Api\V1\AccommodationAvailabilityController::class, 'destroy']);
        
        // Pricing & Yield Management (Phase 22)
        Route::get('/accommodation-pricing-rules', [\App\Http\Controllers\Api\V1\AccommodationPricingRuleController::class, 'index']);
        Route::post('/accommodation-pricing-rules', [\App\Http\Controllers\Api\V1\AccommodationPricingRuleController::class, 'store']);
        Route::delete('/accommodation-pricing-rules/{accommodationPricingRule}', [\App\Http\Controllers\Api\V1\AccommodationPricingRuleController::class, 'destroy']);
        
        // Booking Engine (Phase 23)
        Route::get('/accommodation-bookings', [\App\Http\Controllers\Api\V1\AccommodationBookingController::class, 'index']);
        Route::post('/accommodation-bookings', [\App\Http\Controllers\Api\V1\AccommodationBookingController::class, 'store']);
        Route::patch('/accommodation-bookings/{accommodationBooking}', [\App\Http\Controllers\Api\V1\AccommodationBookingController::class, 'update']);
        
        // Payments & Transactions (Phase 24)
        Route::get('/payments', [\App\Http\Controllers\Api\V1\PaymentController::class, 'index']);
        Route::post('/payments', [\App\Http\Controllers\Api\V1\PaymentController::class, 'store']);
        Route::patch('/payments/{payment}', [\App\Http\Controllers\Api\V1\PaymentController::class, 'update']);
        
        // Companies & Organizations (Phase 26)
        Route::get('/organizations', [\App\Http\Controllers\Api\V1\OrganizationController::class, 'index']);
        Route::post('/organizations', [\App\Http\Controllers\Api\V1\OrganizationController::class, 'store']);
        Route::post('/organizations/{organization}/members', [\App\Http\Controllers\Api\V1\OrganizationController::class, 'addMember']);
        
        // CRM (Phase 27)
        Route::get('/leads', [\App\Http\Controllers\Api\V1\LeadController::class, 'index']);
        Route::post('/leads', [\App\Http\Controllers\Api\V1\LeadController::class, 'store']);
        Route::patch('/leads/{lead}', [\App\Http\Controllers\Api\V1\LeadController::class, 'update']);
        Route::post('/leads/{lead}/activities', [\App\Http\Controllers\Api\V1\LeadController::class, 'addActivity']);
        
        // Development (Phase 28)
        Route::get('/development/projects', [\App\Http\Controllers\Api\V1\DevelopmentController::class, 'index']);
        Route::post('/development/projects', [\App\Http\Controllers\Api\V1\DevelopmentController::class, 'storeProject']);
        Route::post('/development/projects/{project}/buildings', [\App\Http\Controllers\Api\V1\DevelopmentController::class, 'storeBuilding']);
        Route::post('/development/buildings/{building}/units', [\App\Http\Controllers\Api\V1\DevelopmentController::class, 'storeUnit']);
        Route::post('/development/projects/{project}/payment-plans', [\App\Http\Controllers\Api\V1\DevelopmentController::class, 'storePaymentPlan']);
        
        // Rental (Phase 29)
        Route::post('/rental/profile', [\App\Http\Controllers\Api\V1\RentalController::class, 'storeTenantProfile']);
        Route::post('/rental/applications', [\App\Http\Controllers\Api\V1\RentalController::class, 'storeApplication']);
        Route::post('/rental/applications/{application}/approve', [\App\Http\Controllers\Api\V1\RentalController::class, 'approveApplication']);
        
        // Property Management (Phase 30)
        Route::post('/pm/tickets', [\App\Http\Controllers\Api\V1\PropertyManagementController::class, 'storeTicket']);
        Route::patch('/pm/tickets/{ticket}/status', [\App\Http\Controllers\Api\V1\PropertyManagementController::class, 'updateTicketStatus']);
        Route::post('/pm/cleaning-tasks', [\App\Http\Controllers\Api\V1\PropertyManagementController::class, 'storeCleaningTask']);
        Route::patch('/pm/cleaning-tasks/{task}/status', [\App\Http\Controllers\Api\V1\PropertyManagementController::class, 'updateCleaningStatus']);
        
        // Community (Phase 31)
        Route::post('/communities', [\App\Http\Controllers\Api\V1\CommunityController::class, 'storeCommunity']);
        Route::post('/communities/{community}/facilities', [\App\Http\Controllers\Api\V1\CommunityController::class, 'storeFacility']);
        Route::post('/facilities/{facility}/bookings', [\App\Http\Controllers\Api\V1\CommunityController::class, 'storeBooking']);
        
        // Documents & Contracts (Phase 32)
        Route::post('/documents/templates', [\App\Http\Controllers\Api\V1\DocumentController::class, 'storeTemplate']);
        Route::post('/documents/contracts', [\App\Http\Controllers\Api\V1\DocumentController::class, 'generateContract']);
        Route::post('/documents/contracts/{contract}/sign', [\App\Http\Controllers\Api\V1\DocumentController::class, 'signContract']);
        
        // Financials & Ledger (Phase 33)
        Route::post('/financials/accounts', [\App\Http\Controllers\Api\V1\FinancialsController::class, 'storeAccount']);
        Route::post('/financials/ledger-entries', [\App\Http\Controllers\Api\V1\FinancialsController::class, 'recordDoubleEntry']);
        Route::post('/financials/invoices', [\App\Http\Controllers\Api\V1\FinancialsController::class, 'storeInvoice']);
        
        // Reports & Analytics (Phase 34)
        Route::get('/analytics/dashboard', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'dashboard']);
        Route::post('/analytics/templates', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'createTemplate']);
        Route::post('/analytics/reports', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'generateReport']);
        
        // Marketing & Leads (Phase 35)
        Route::post('/marketing/campaigns', [\App\Http\Controllers\Api\V1\MarketingController::class, 'storeCampaign']);
        Route::post('/marketing/leads', [\App\Http\Controllers\Api\V1\MarketingController::class, 'storeLead']);
        Route::post('/marketing/leads/{lead}/convert', [\App\Http\Controllers\Api\V1\MarketingController::class, 'convertLead']);
    });
});

// Public Routes
Route::prefix('v1')->group(function () {
    Route::get('/public/listings', [\App\Http\Controllers\Api\V1\PublicListingController::class, 'index']);
    Route::get('/public/listings/{listing}', [\App\Http\Controllers\Api\V1\PublicListingController::class, 'show']);
    
    Route::get('/search', [\App\Http\Controllers\Api\V1\SearchController::class, 'search']);
});
