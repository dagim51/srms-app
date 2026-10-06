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
    });
});

// Public Routes
Route::prefix('v1')->group(function () {
    Route::get('/public/listings', [\App\Http\Controllers\Api\V1\PublicListingController::class, 'index']);
    Route::get('/public/listings/{listing}', [\App\Http\Controllers\Api\V1\PublicListingController::class, 'show']);
    
    Route::get('/search', [\App\Http\Controllers\Api\V1\SearchController::class, 'search']);
});
