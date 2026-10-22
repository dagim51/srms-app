<?php

use App\Http\Controllers\Web\AccommodationCalendarWebController;
use App\Http\Controllers\Web\AccommodationPricingWebController;
use App\Http\Controllers\Web\AccommodationWebController;
use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\AgentWebController;
use App\Http\Controllers\Web\AiSearchWebController;
use App\Http\Controllers\Web\AlertsWebController;
use App\Http\Controllers\Web\AmenityCatalogController;
use App\Http\Controllers\Web\AnalyticsWebController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingEngineWebController;
use App\Http\Controllers\Web\CollectionWebController;
use App\Http\Controllers\Web\CrmWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\DeveloperProjectWebController;
use App\Http\Controllers\Web\DeveloperSalesWebController;
use App\Http\Controllers\Web\DocumentWebController;
use App\Http\Controllers\Web\FavoriteWebController;
use App\Http\Controllers\Web\FeatureCatalogController;
use App\Http\Controllers\Web\FinancialWebController;
use App\Http\Controllers\Web\GisWebController;
use App\Http\Controllers\Web\IntegrationsWebController;
use App\Http\Controllers\Web\IntelligenceWebController;
use App\Http\Controllers\Web\ListingController;
use App\Http\Controllers\Web\ListingModerationWebController;
use App\Http\Controllers\Web\ListingSubmissionWebController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\LocationWebController;
use App\Http\Controllers\Web\MessageWebController;
use App\Http\Controllers\Web\NotificationWebController;
use App\Http\Controllers\Web\OfferWebController;
use App\Http\Controllers\Web\OperationsWebController;
use App\Http\Controllers\Web\OrganizationWebController;
use App\Http\Controllers\Web\PropertyMediaWebController;
use App\Http\Controllers\Web\PropertyWebController;
use App\Http\Controllers\Web\PublicPropertyWebController;
use App\Http\Controllers\Web\RentalWebController;
use App\Http\Controllers\Web\ReservationWebController;
use App\Http\Controllers\Web\ReviewWebController;
use App\Http\Controllers\Web\SearchWebController;
use App\Http\Controllers\Web\ServicesWebController;
use App\Http\Controllers\Web\SettingsWebController;
use App\Http\Controllers\Web\TransactionWebController;
use App\Http\Controllers\Web\TrustWebController;
use App\Http\Controllers\Web\ViewingWebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Localization / Language Switcher Route
Route::match(['get', 'post'], '/locale/{locale?}', [LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Pages that must remain reachable without authentication.
*/

// Phase 13: Public Property Discovery & Marketplace
Route::get('/', [PublicPropertyWebController::class, 'home'])->name('home');
Route::get('/properties', [PublicPropertyWebController::class, 'index'])->name('properties.index');
Route::get('/properties/{id}', [PublicPropertyWebController::class, 'show'])->name('properties.show');
Route::get('/discover', [PublicPropertyWebController::class, 'index'])->name('properties.discover');
Route::get('/short-stay', [PublicPropertyWebController::class, 'shortStay'])->name('short-stay');

// Phase 14: Search Engine UI
Route::get('/search', [SearchWebController::class, 'index'])->name('search');

// Phase 20: Public Accommodation Discovery (Short-Stay)
Route::get('/accommodations', [AccommodationWebController::class, 'search'])->name('accommodations.search');
Route::get('/accommodations/{accommodation}', [AccommodationWebController::class, 'showPublic'])->name('accommodations.show');

// Phase 28: Developer Projects (public)
Route::get('/developers', [DeveloperProjectWebController::class, 'publicIndex'])->name('developers.index');
Route::get('/projects/{project}', [DeveloperProjectWebController::class, 'publicShow'])->name('projects.show');

// Phase 42: Professional Services Marketplace (public)
Route::get('/services', [ServicesWebController::class, 'publicIndex'])->name('services.index');
Route::get('/services/{id}', [ServicesWebController::class, 'publicShow'])->name('services.show');

// Public Agent Profile
Route::get('/agents/{id}', [AgentWebController::class, 'show'])->name('agent.show');

Route::get('/403', function () {
    return view('errors.403');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::get('/reset-password/{token}', function (Request $request, $token) {
        return view('auth.reset-password', ['request' => $request]);
    })->name('password.reset');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| All dashboard, account, booking, review and marketplace-engagement routes
| require an authenticated session.
*/

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics-data', [DashboardWebController::class, 'data'])->name('dashboard.analytics.data');

    // Phase 15: Favorites & Collections UI
    Route::get('/dashboard/favorites', [FavoriteWebController::class, 'index'])->name('dashboard.favorites.index');
    Route::get('/favorites', [FavoriteWebController::class, 'index'])->name('favorites');
    Route::post('/favorites/toggle/{property}', [FavoriteWebController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/dashboard/favorites/{favorite}', [FavoriteWebController::class, 'destroy'])->name('dashboard.favorites.destroy');

    Route::get('/dashboard/collections', [CollectionWebController::class, 'index'])->name('dashboard.collections.index');
    Route::post('/dashboard/collections', [CollectionWebController::class, 'store'])->name('dashboard.collections.store');
    Route::get('/dashboard/collections/{collection}', [CollectionWebController::class, 'show'])->name('dashboard.collections.show');
    Route::put('/dashboard/collections/{collection}', [CollectionWebController::class, 'update'])->name('dashboard.collections.update');
    Route::delete('/dashboard/collections/{collection}', [CollectionWebController::class, 'destroy'])->name('dashboard.collections.destroy');
    Route::post('/dashboard/collections/{collection}/properties', [CollectionWebController::class, 'addProperty'])->name('dashboard.collections.properties.add');
    Route::delete('/dashboard/collections/{collection}/properties/{property}', [CollectionWebController::class, 'removeProperty'])->name('dashboard.collections.properties.remove');
    Route::post('/dashboard/collections/{collection}/properties/{property}/move', [CollectionWebController::class, 'moveProperty'])->name('dashboard.collections.properties.move');

    // Phase 16: Inquiry & Messaging UI
    Route::get('/dashboard/messages', [MessageWebController::class, 'index'])->name('dashboard.messages.index');
    Route::get('/dashboard/messages/{conversation}', [MessageWebController::class, 'show'])->name('dashboard.messages.show');
    Route::post('/dashboard/messages/{conversation}', [MessageWebController::class, 'storeMessage'])->name('dashboard.messages.store');
    Route::post('/dashboard/inquiries', [MessageWebController::class, 'storeInquiry'])->name('dashboard.inquiries.store');

    // Phase 17: Property Viewing UI
    Route::get('/dashboard/viewings', [ViewingWebController::class, 'index'])->name('dashboard.viewings.index');
    Route::get('/dashboard/viewings/create', [ViewingWebController::class, 'create'])->name('dashboard.viewings.create');
    Route::post('/dashboard/viewings', [ViewingWebController::class, 'store'])->name('dashboard.viewings.store');
    Route::get('/dashboard/viewings/{viewing}', [ViewingWebController::class, 'show'])->name('dashboard.viewings.show');
    Route::put('/dashboard/viewings/{viewing}', [ViewingWebController::class, 'update'])->name('dashboard.viewings.update');

    // Phase 18: Offers & Negotiation UI
    Route::get('/dashboard/offers', [OfferWebController::class, 'index'])->name('dashboard.offers.index');
    Route::get('/dashboard/offers/create', [OfferWebController::class, 'create'])->name('dashboard.offers.create');
    Route::post('/dashboard/offers', [OfferWebController::class, 'store'])->name('dashboard.offers.store');
    Route::get('/dashboard/offers/{offer}', [OfferWebController::class, 'show'])->name('dashboard.offers.show');
    Route::put('/dashboard/offers/{offer}', [OfferWebController::class, 'update'])->name('dashboard.offers.update');

    // Phase 19: Marketplace Reservations UI
    Route::get('/dashboard/reservations', [ReservationWebController::class, 'index'])->name('dashboard.reservations.index');
    Route::get('/dashboard/reservations/create', [ReservationWebController::class, 'create'])->name('dashboard.reservations.create');
    Route::post('/dashboard/reservations', [ReservationWebController::class, 'store'])->name('dashboard.reservations.store');
    Route::get('/dashboard/reservations/{reservation}', [ReservationWebController::class, 'show'])->name('dashboard.reservations.show');
    Route::put('/dashboard/reservations/{reservation}', [ReservationWebController::class, 'update'])->name('dashboard.reservations.update');

    // Phase 20: Accommodation & Host UI
    Route::resource('/dashboard/accommodations', AccommodationWebController::class)->names('dashboard.accommodations')->except(['destroy']);

    // Phase 21: Availability & Calendar UI
    Route::get('/dashboard/accommodations/{accommodation}/calendar', [AccommodationCalendarWebController::class, 'show'])->name('dashboard.accommodations.calendar');
    Route::post('/dashboard/accommodations/{accommodation}/calendar/block', [AccommodationCalendarWebController::class, 'block'])->name('dashboard.accommodations.calendar.block');
    Route::delete('/dashboard/accommodations/{accommodation}/calendar/unblock/{availability}', [AccommodationCalendarWebController::class, 'unblock'])->name('dashboard.accommodations.calendar.unblock');

    // Phase 22: Stay Pricing & Cancellation UI
    Route::get('/dashboard/accommodations/{accommodation}/pricing', [AccommodationPricingWebController::class, 'edit'])->name('dashboard.accommodations.pricing');
    Route::put('/dashboard/accommodations/{accommodation}/pricing', [AccommodationPricingWebController::class, 'update'])->name('dashboard.accommodations.pricing.update');
    Route::post('/dashboard/accommodations/{accommodation}/pricing/rules', [AccommodationPricingWebController::class, 'storeRule'])->name('dashboard.accommodations.pricing.rules.store');
    Route::delete('/dashboard/accommodations/{accommodation}/pricing/rules/{rule}', [AccommodationPricingWebController::class, 'destroyRule'])->name('dashboard.accommodations.pricing.rules.destroy');

    // Phase 23: Booking Engine UI
    Route::get('/accommodations/{accommodation}/book', [BookingEngineWebController::class, 'create'])->name('booking.create');
    Route::post('/accommodations/{accommodation}/book', [BookingEngineWebController::class, 'store'])->name('booking.store');
    Route::get('/bookings/{booking}', [BookingEngineWebController::class, 'show'])->name('booking.show');
    Route::post('/bookings/{booking}/confirm', [BookingEngineWebController::class, 'confirm'])->name('booking.confirm');
    Route::post('/bookings/{booking}/cancel', [BookingEngineWebController::class, 'cancel'])->name('booking.cancel');
    Route::post('/bookings/{booking}/check-in', [BookingEngineWebController::class, 'checkIn'])->name('booking.check-in');
    Route::post('/bookings/{booking}/check-out', [BookingEngineWebController::class, 'checkOut'])->name('booking.check-out');

    // Phase 24: Reviews UI
    Route::get('/bookings/{booking}/review', [ReviewWebController::class, 'create'])->name('booking.review.create');
    Route::post('/bookings/{booking}/review', [ReviewWebController::class, 'store'])->name('booking.review.store');
    Route::get('/dashboard/reviews', [ReviewWebController::class, 'index'])->name('dashboard.reviews.index');

    // Phase 25: Notifications & Automation UI
    Route::get('/dashboard/notifications', [NotificationWebController::class, 'index'])->name('dashboard.notifications.index');
    Route::patch('/dashboard/notifications/{id}/read', [NotificationWebController::class, 'markAsRead'])->name('dashboard.notifications.read');
    Route::get('/dashboard/preferences', [NotificationWebController::class, 'preferences'])->name('dashboard.preferences');
    Route::put('/dashboard/preferences', [NotificationWebController::class, 'updatePreferences'])->name('dashboard.preferences.update');

    // Phase 26: Companies, Branches & Agents UI
    Route::get('/dashboard/organization', [OrganizationWebController::class, 'index'])->name('dashboard.organization.index');
    Route::get('/dashboard/organization/create', [OrganizationWebController::class, 'create'])->name('dashboard.organization.create');
    Route::post('/dashboard/organization', [OrganizationWebController::class, 'store'])->name('dashboard.organization.store');
    Route::get('/dashboard/organization/profile', [OrganizationWebController::class, 'profile'])->name('dashboard.organization.profile');
    Route::get('/dashboard/organization/branches', [OrganizationWebController::class, 'branches'])->name('dashboard.organization.branches');
    Route::get('/dashboard/organization/teams', [OrganizationWebController::class, 'teams'])->name('dashboard.organization.teams');
    Route::get('/dashboard/organization/members', [OrganizationWebController::class, 'members'])->name('dashboard.organization.members');

    // Phase 27: CRM UI
    Route::get('/dashboard/crm', [CrmWebController::class, 'index'])->name('dashboard.crm.index');
    Route::get('/dashboard/crm/leads', [CrmWebController::class, 'leads'])->name('dashboard.crm.leads');
    Route::get('/dashboard/crm/leads/{lead}', [CrmWebController::class, 'leadShow'])->name('dashboard.crm.leads.show');
    Route::get('/dashboard/crm/pipeline', [CrmWebController::class, 'pipeline'])->name('dashboard.crm.pipeline');
    Route::get('/dashboard/crm/clients', [CrmWebController::class, 'clients'])->name('dashboard.crm.clients');
    Route::get('/dashboard/crm/tasks', [CrmWebController::class, 'tasks'])->name('dashboard.crm.tasks');
    Route::get('/dashboard/crm/commissions', [CrmWebController::class, 'commissions'])->name('dashboard.crm.commissions');

    // Phase 28: Developer Projects UI (dashboard)
    Route::get('/dashboard/projects', [DeveloperProjectWebController::class, 'index'])->name('dashboard.projects.index');
    Route::get('/dashboard/projects/{project}', [DeveloperProjectWebController::class, 'show'])->name('dashboard.projects.show');
    Route::get('/dashboard/projects/{project}/buildings', [DeveloperProjectWebController::class, 'buildings'])->name('dashboard.projects.buildings');
    Route::get('/dashboard/projects/{project}/units', [DeveloperProjectWebController::class, 'units'])->name('dashboard.projects.units');
    Route::get('/dashboard/projects/{project}/progress', [DeveloperProjectWebController::class, 'progress'])->name('dashboard.projects.progress');
    Route::get('/dashboard/projects/{project}/documents', [DeveloperProjectWebController::class, 'documents'])->name('dashboard.projects.documents');

    // Phase 29: Developer Sales UI
    Route::get('/dashboard/sales', function () {
        return redirect()->route('dashboard.sales.units');
    })->name('dashboard.sales.index');
    Route::get('/dashboard/sales/units', [DeveloperSalesWebController::class, 'units'])->name('dashboard.sales.units');
    Route::get('/dashboard/sales/units/{id}', [DeveloperSalesWebController::class, 'unitDetail'])->name('dashboard.sales.units.show');
    Route::get('/dashboard/sales/units/{id}/reservation', [DeveloperSalesWebController::class, 'reservation'])->name('dashboard.sales.reservation');
    Route::get('/dashboard/sales/buyers', [DeveloperSalesWebController::class, 'buyers'])->name('dashboard.sales.buyers');
    Route::get('/dashboard/sales/buyers/{id}', [DeveloperSalesWebController::class, 'buyerDetail'])->name('dashboard.sales.buyers.show');
    Route::get('/dashboard/sales/payment-plans', [DeveloperSalesWebController::class, 'paymentPlans'])->name('dashboard.sales.payment_plans');
    Route::get('/dashboard/sales/contracts', [DeveloperSalesWebController::class, 'contracts'])->name('dashboard.sales.contracts');
    Route::get('/dashboard/sales/handovers', [DeveloperSalesWebController::class, 'handovers'])->name('dashboard.sales.handovers');

    // Transactions & Deals Pipeline UI
    Route::get('/dashboard/transactions', [TransactionWebController::class, 'index'])->name('dashboard.transactions');
    Route::get('/dashboard/transactions/{id}', [TransactionWebController::class, 'show'])->name('dashboard.transactions.show');

    // Phase 30: Rental & Lease Management UI
    Route::get('/dashboard/rental', [RentalWebController::class, 'index'])->name('dashboard.rental.index');
    Route::get('/dashboard/rental/applications', [RentalWebController::class, 'applications'])->name('dashboard.rental.applications');
    Route::get('/dashboard/rental/tenants', [RentalWebController::class, 'tenants'])->name('dashboard.rental.tenants');
    Route::get('/dashboard/rental/tenants/{id}', [RentalWebController::class, 'tenantShow'])->name('dashboard.rental.tenants.show');
    Route::get('/dashboard/rental/leases', [RentalWebController::class, 'leases'])->name('dashboard.rental.leases');
    Route::get('/dashboard/rental/leases/{id}', [RentalWebController::class, 'leaseShow'])->name('dashboard.rental.leases.show');
    Route::get('/dashboard/rental/payments', [RentalWebController::class, 'payments'])->name('dashboard.rental.payments');

    // Phase 31: Operations UI
    Route::get('/dashboard/operations', [OperationsWebController::class, 'index'])->name('dashboard.operations.index');
    Route::get('/dashboard/operations/tickets', [OperationsWebController::class, 'tickets'])->name('dashboard.operations.tickets');
    Route::get('/dashboard/operations/tickets/{id}', [OperationsWebController::class, 'ticketShow'])->name('dashboard.operations.tickets.show');
    Route::get('/dashboard/operations/cleaning', [OperationsWebController::class, 'cleaning'])->name('dashboard.operations.cleaning');
    Route::get('/dashboard/operations/staff', [OperationsWebController::class, 'staff'])->name('dashboard.operations.staff');

    // Phase 32: Finance UI
    Route::get('/dashboard/finance', [FinancialWebController::class, 'index'])->name('dashboard.finance.index');
    Route::get('/dashboard/finance/transactions', [FinancialWebController::class, 'transactions'])->name('dashboard.finance.transactions');
    Route::get('/dashboard/finance/transactions/{id}', [FinancialWebController::class, 'transactionShow'])->name('dashboard.finance.transactions.show');
    Route::get('/dashboard/finance/invoices', [FinancialWebController::class, 'invoices'])->name('dashboard.finance.invoices');
    Route::get('/dashboard/finance/ledger', [FinancialWebController::class, 'ledger'])->name('dashboard.finance.ledger');

    // Phase 33: Documents & Verification UI
    Route::get('/dashboard/documents', [DocumentWebController::class, 'index'])->name('dashboard.documents.index');
    Route::get('/dashboard/documents/files/{id}', [DocumentWebController::class, 'documentShow'])->name('dashboard.documents.show');
    Route::get('/dashboard/documents/verifications', [DocumentWebController::class, 'verifications'])->name('dashboard.documents.verifications');
    Route::get('/dashboard/documents/verifications/{id}', [DocumentWebController::class, 'verificationShow'])->name('dashboard.documents.verifications.show');

    // Phase 34: Fraud, Reporting & Disputes UI
    Route::get('/dashboard/trust', [TrustWebController::class, 'index'])->name('dashboard.trust.index');
    Route::get('/dashboard/trust/reports', [TrustWebController::class, 'reports'])->name('dashboard.trust.reports');
    Route::get('/dashboard/trust/disputes', [TrustWebController::class, 'disputes'])->name('dashboard.trust.disputes');
    Route::get('/dashboard/trust/cases/{id}', [TrustWebController::class, 'caseShow'])->name('dashboard.trust.cases.show');

    // Phase 35: Administration Platform UI (Restricted to Administrator role)
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/dashboard/admin', [AdminWebController::class, 'index'])->name('dashboard.admin.index');
        Route::get('/dashboard/admin/users', [AdminWebController::class, 'users'])->name('dashboard.admin.users');
        Route::get('/dashboard/admin/users/create', [AdminWebController::class, 'userCreate'])->name('dashboard.admin.users.create');
        Route::post('/dashboard/admin/users', [AdminWebController::class, 'userStore'])->name('dashboard.admin.users.store');
        Route::post('/dashboard/admin/users/{id}/role', [AdminWebController::class, 'updateUserRole'])->name('dashboard.admin.users.role');
        Route::get('/dashboard/users', [AdminWebController::class, 'users'])->name('dashboard.users');
        Route::get('/dashboard/users/create', [AdminWebController::class, 'userCreate'])->name('dashboard.users.create');
        Route::post('/dashboard/users', [AdminWebController::class, 'userStore'])->name('dashboard.users.store');
        Route::get('/dashboard/admin/users/{id}', [AdminWebController::class, 'userShow'])->name('dashboard.admin.users.show');
        Route::get('/dashboard/admin/permissions', [AdminWebController::class, 'permissions'])->name('dashboard.admin.permissions');
        Route::post('/dashboard/admin/permissions', [AdminWebController::class, 'updatePermissions'])->name('dashboard.admin.permissions.update');
        Route::get('/dashboard/admin/audit', [AdminWebController::class, 'audit'])->name('dashboard.admin.audit');
        Route::get('/dashboard/admin/settings', [AdminWebController::class, 'settings'])->name('dashboard.admin.settings');
    });

    // Account & Platform Settings
    Route::get('/dashboard/settings', [SettingsWebController::class, 'index'])->name('dashboard.settings');
    Route::post('/dashboard/settings/profile', [SettingsWebController::class, 'updateProfile'])->name('dashboard.settings.profile');
    Route::post('/dashboard/settings/security', [SettingsWebController::class, 'updateSecurity'])->name('dashboard.settings.security');
    Route::post('/dashboard/settings/notifications', [SettingsWebController::class, 'updateNotifications'])->name('dashboard.settings.notifications');
    Route::post('/dashboard/settings/platform', [SettingsWebController::class, 'updatePlatform'])->name('dashboard.settings.platform');
    Route::get('/dashboard/profile', function () {
        return redirect()->route('dashboard.settings', ['tab' => 'profile']);
    })->name('dashboard.profile');

    // Phase 36: Reporting & Analytics UI
    Route::get('/dashboard/analytics', [AnalyticsWebController::class, 'index'])->name('dashboard.analytics.index');
    Route::get('/dashboard/analytics/reports', [AnalyticsWebController::class, 'reports'])->name('dashboard.analytics.reports');
    Route::get('/dashboard/analytics/exports', [AnalyticsWebController::class, 'exports'])->name('dashboard.analytics.exports');

    // Phase 37: Search Alerts & Recommendations UI
    Route::get('/dashboard/alerts', [AlertsWebController::class, 'index'])->name('dashboard.alerts.index');
    Route::get('/dashboard/alerts/{id}', [AlertsWebController::class, 'show'])->name('dashboard.alerts.show');
    Route::get('/dashboard/recommendations', [AlertsWebController::class, 'recommendations'])->name('dashboard.recommendations.index');

    // Phase 39: Advanced GIS UI
    Route::get('/dashboard/map', [GisWebController::class, 'index'])->name('dashboard.gis.index');

    // Phase 41: Valuation & Investment Intelligence UI
    Route::get('/dashboard/intelligence/valuation', [IntelligenceWebController::class, 'valuation'])->name('dashboard.intelligence.valuation');
    Route::get('/dashboard/intelligence/calculators', [IntelligenceWebController::class, 'calculators'])->name('dashboard.intelligence.calculators');
    Route::get('/dashboard/intelligence/portfolio', [IntelligenceWebController::class, 'portfolio'])->name('dashboard.intelligence.portfolio');

    // Phase 42: Professional Services Marketplace UI (dashboard)
    Route::get('/dashboard/services', [ServicesWebController::class, 'index'])->name('dashboard.services.index');
    Route::get('/dashboard/services/provider', [ServicesWebController::class, 'dashboard'])->name('dashboard.services.dashboard');
    Route::get('/dashboard/services/{id}', [ServicesWebController::class, 'show'])->name('dashboard.services.show');

    // Phase 43: Advanced Integrations UI
    Route::get('/dashboard/integrations', [IntegrationsWebController::class, 'index'])->name('dashboard.integrations.index');
    Route::get('/dashboard/integrations/history', [IntegrationsWebController::class, 'history'])->name('dashboard.integrations.history');

    // Phase 40: AI Search & Listing Assistant UI
    Route::get('/dashboard/ai/search', [AiSearchWebController::class, 'search'])->name('dashboard.ai.search');
    Route::match(['get', 'post'], '/dashboard/ai/assistant', [AiSearchWebController::class, 'assistant'])->name('dashboard.ai.assistant');

    // Dashboard properties (PropertyWebController)
    Route::get('/dashboard/properties', [PropertyWebController::class, 'index'])->name('dashboard.properties.index');
    Route::get('/dashboard/properties/create', [PropertyWebController::class, 'create'])->name('dashboard.properties.create');
    Route::post('/dashboard/properties', [PropertyWebController::class, 'store'])->name('dashboard.properties.store');
    Route::get('/dashboard/properties/{property}/edit', [PropertyWebController::class, 'edit'])->name('dashboard.properties.edit');
    Route::put('/dashboard/properties/{property}', [PropertyWebController::class, 'update'])->name('dashboard.properties.update');
    Route::delete('/dashboard/properties/{property}', [PropertyWebController::class, 'destroy'])->name('dashboard.properties.destroy');

    // Phase 11: Listing Core UI
    Route::get('/dashboard/listings', [ListingController::class, 'index'])->name('dashboard.listings.index');
    Route::get('/dashboard/listings/create', [ListingController::class, 'create'])->name('dashboard.listings.create');
    Route::post('/dashboard/listings', [ListingController::class, 'store'])->name('dashboard.listings.store');
    Route::get('/dashboard/listings/{listing}/edit', [ListingController::class, 'edit'])->name('dashboard.listings.edit');
    Route::put('/dashboard/listings/{listing}', [ListingController::class, 'update'])->name('dashboard.listings.update');
    Route::patch('/dashboard/listings/{listing}/toggle-status', [ListingController::class, 'toggleStatus'])->name('dashboard.listings.toggle-status');
    Route::delete('/dashboard/listings/{listing}', [ListingController::class, 'destroy'])->name('dashboard.listings.destroy');
    Route::get('/dashboard/listings/{listing}/preview', [ListingController::class, 'preview'])->name('dashboard.listings.preview');

    // Phase 12: Listing Submission & Lifecycle Status (User Screens)
    Route::get('/dashboard/listings/{listing}/submit', [ListingSubmissionWebController::class, 'showSubmitForm'])->name('dashboard.listings.submit');
    Route::post('/dashboard/listings/{listing}/submit', [ListingSubmissionWebController::class, 'processSubmit'])->name('dashboard.listings.submit.process');
    Route::get('/dashboard/listings/{listing}/submission-status', [ListingSubmissionWebController::class, 'status'])->name('dashboard.listings.submission-status');

    // Phase 12: Listing Moderation Queue & Decision Engine (Moderator Screens)
    Route::get('/dashboard/moderation/listings', [ListingModerationWebController::class, 'index'])->name('dashboard.moderation.listings.index');
    Route::get('/dashboard/moderation/listings/{listing}', [ListingModerationWebController::class, 'show'])->name('dashboard.moderation.listings.show');
    Route::post('/dashboard/moderation/listings/{listing}/decision', [ListingModerationWebController::class, 'decision'])->name('dashboard.moderation.listings.decision');

    // Phase 08: Property Features & Amenities Catalog Management
    Route::get('/dashboard/amenities', [AmenityCatalogController::class, 'index'])->name('dashboard.amenities.index');
    Route::post('/dashboard/amenities', [AmenityCatalogController::class, 'store'])->name('dashboard.amenities.store');
    Route::put('/dashboard/amenities/{amenity}', [AmenityCatalogController::class, 'update'])->name('dashboard.amenities.update');
    Route::patch('/dashboard/amenities/{amenity}/toggle-status', [AmenityCatalogController::class, 'toggleStatus'])->name('dashboard.amenities.toggle');
    Route::delete('/dashboard/amenities/{amenity}', [AmenityCatalogController::class, 'destroy'])->name('dashboard.amenities.destroy');

    Route::get('/dashboard/features', [FeatureCatalogController::class, 'index'])->name('dashboard.features.index');
    Route::post('/dashboard/features', [FeatureCatalogController::class, 'store'])->name('dashboard.features.store');
    Route::put('/dashboard/features/{feature}', [FeatureCatalogController::class, 'update'])->name('dashboard.features.update');
    Route::patch('/dashboard/features/{feature}/toggle-status', [FeatureCatalogController::class, 'toggleStatus'])->name('dashboard.features.toggle');
    Route::delete('/dashboard/features/{feature}', [FeatureCatalogController::class, 'destroy'])->name('dashboard.features.destroy');

    // Location Hierarchy Management (Zones, Sub-Cities, Woredas)
    Route::get('/dashboard/locations', [LocationWebController::class, 'index'])->name('dashboard.locations.index');
    Route::post('/dashboard/locations/sub-cities', [LocationWebController::class, 'storeSubCity'])->name('dashboard.locations.subcities.store');
    Route::put('/dashboard/locations/sub-cities/{subCity}', [LocationWebController::class, 'updateSubCity'])->name('dashboard.locations.subcities.update');
    Route::post('/dashboard/locations/zones', [LocationWebController::class, 'storeZone'])->name('dashboard.locations.zones.store');
    Route::put('/dashboard/locations/zones/{zone}', [LocationWebController::class, 'updateZone'])->name('dashboard.locations.zones.update');
    Route::post('/dashboard/locations/woredas', [LocationWebController::class, 'storeWoreda'])->name('dashboard.locations.woredas.store');
    Route::put('/dashboard/locations/woredas/{woreda}', [LocationWebController::class, 'updateWoreda'])->name('dashboard.locations.woredas.update');
    Route::patch('/dashboard/locations/toggle/{type}/{id}', [LocationWebController::class, 'toggleStatus'])->name('dashboard.locations.toggle');
    Route::delete('/dashboard/locations/{type}/{id}', [LocationWebController::class, 'destroy'])->name('dashboard.locations.destroy');
    Route::get('/dashboard/locations/api/cascade', [LocationWebController::class, 'cascadeData'])->name('dashboard.locations.api.cascade');

    // Phase 09: Property Media UI
    Route::get('/dashboard/properties/media', [PropertyMediaWebController::class, 'index'])->name('dashboard.properties.media.demo');
    Route::get('/dashboard/properties/{property}/media', [PropertyMediaWebController::class, 'index'])->name('dashboard.properties.media');
    Route::post('/dashboard/properties/{property}/media', [PropertyMediaWebController::class, 'store'])->name('dashboard.properties.media.store');
    Route::put('/dashboard/properties/{property}/media/order', [PropertyMediaWebController::class, 'updateOrder'])->name('dashboard.properties.media.order');
    Route::patch('/dashboard/properties/{property}/media/{media}/set-primary', [PropertyMediaWebController::class, 'setPrimary'])->name('dashboard.properties.media.primary');
    Route::delete('/dashboard/properties/{property}/media/{media}', [PropertyMediaWebController::class, 'destroy'])->name('dashboard.properties.media.destroy');

    // Phase 10: GIS Foundation UI
    Route::get('/dashboard/properties/map', function () {
        return view('dashboard.properties.map');
    })->name('dashboard.properties.map');
});
