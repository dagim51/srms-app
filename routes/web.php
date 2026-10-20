<?php

use Illuminate\Support\Facades\Route;

// Localization / Language Switcher Route
Route::match(['get', 'post'], '/locale/{locale?}', [\App\Http\Controllers\Web\LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Pages that must remain reachable without authentication.
*/

// Phase 13: Public Property Discovery & Marketplace
Route::get('/', [\App\Http\Controllers\Web\PublicPropertyWebController::class, 'home'])->name('home');
Route::get('/properties', [\App\Http\Controllers\Web\PublicPropertyWebController::class, 'index'])->name('properties.index');
Route::get('/properties/{id}', [\App\Http\Controllers\Web\PublicPropertyWebController::class, 'show'])->name('properties.show');
Route::get('/discover', [\App\Http\Controllers\Web\PublicPropertyWebController::class, 'index'])->name('properties.discover');
Route::get('/short-stay', [\App\Http\Controllers\Web\PublicPropertyWebController::class, 'shortStay'])->name('short-stay');

// Phase 14: Search Engine UI
Route::get('/search', [\App\Http\Controllers\Web\SearchWebController::class, 'index'])->name('search');

// Phase 20: Public Accommodation Discovery (Short-Stay)
Route::get('/accommodations', [\App\Http\Controllers\Web\AccommodationWebController::class, 'search'])->name('accommodations.search');
Route::get('/accommodations/{accommodation}', [\App\Http\Controllers\Web\AccommodationWebController::class, 'showPublic'])->name('accommodations.show');

// Phase 28: Developer Projects (public)
Route::get('/developers', [\App\Http\Controllers\Web\DeveloperProjectWebController::class, 'publicIndex'])->name('developers.index');
Route::get('/projects/{project}', [\App\Http\Controllers\Web\DeveloperProjectWebController::class, 'publicShow'])->name('projects.show');

// Phase 42: Professional Services Marketplace (public)
Route::get('/services', [\App\Http\Controllers\Web\ServicesWebController::class, 'publicIndex'])->name('services.index');
Route::get('/services/{id}', [\App\Http\Controllers\Web\ServicesWebController::class, 'publicShow'])->name('services.show');

// Public Agent Profile
Route::get('/agents/{id}', [\App\Http\Controllers\Web\AgentWebController::class, 'show'])->name('agent.show');

Route::get('/403', function () {
    return view('errors.403');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Web\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Web\AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::get('/register', [\App\Http\Controllers\Web\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Web\AuthController::class, 'register'])->middleware('throttle:6,1');

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::get('/reset-password/{token}', function (\Illuminate\Http\Request $request, $token) {
        return view('auth.reset-password', ['request' => $request]);
    })->name('password.reset');
});

Route::post('/logout', [\App\Http\Controllers\Web\AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| All dashboard, account, booking, review and marketplace-engagement routes
| require an authenticated session.
*/

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Web\DashboardWebController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics-data', [\App\Http\Controllers\Web\DashboardWebController::class, 'data'])->name('dashboard.analytics.data');

    // Phase 15: Favorites & Collections UI
    Route::get('/dashboard/favorites', [\App\Http\Controllers\Web\FavoriteWebController::class, 'index'])->name('dashboard.favorites.index');
    Route::get('/favorites', [\App\Http\Controllers\Web\FavoriteWebController::class, 'index'])->name('favorites');
    Route::post('/favorites/toggle/{property}', [\App\Http\Controllers\Web\FavoriteWebController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/dashboard/favorites/{favorite}', [\App\Http\Controllers\Web\FavoriteWebController::class, 'destroy'])->name('dashboard.favorites.destroy');

    Route::get('/dashboard/collections', [\App\Http\Controllers\Web\CollectionWebController::class, 'index'])->name('dashboard.collections.index');
    Route::post('/dashboard/collections', [\App\Http\Controllers\Web\CollectionWebController::class, 'store'])->name('dashboard.collections.store');
    Route::get('/dashboard/collections/{collection}', [\App\Http\Controllers\Web\CollectionWebController::class, 'show'])->name('dashboard.collections.show');
    Route::put('/dashboard/collections/{collection}', [\App\Http\Controllers\Web\CollectionWebController::class, 'update'])->name('dashboard.collections.update');
    Route::delete('/dashboard/collections/{collection}', [\App\Http\Controllers\Web\CollectionWebController::class, 'destroy'])->name('dashboard.collections.destroy');
    Route::post('/dashboard/collections/{collection}/properties', [\App\Http\Controllers\Web\CollectionWebController::class, 'addProperty'])->name('dashboard.collections.properties.add');
    Route::delete('/dashboard/collections/{collection}/properties/{property}', [\App\Http\Controllers\Web\CollectionWebController::class, 'removeProperty'])->name('dashboard.collections.properties.remove');
    Route::post('/dashboard/collections/{collection}/properties/{property}/move', [\App\Http\Controllers\Web\CollectionWebController::class, 'moveProperty'])->name('dashboard.collections.properties.move');

    // Phase 16: Inquiry & Messaging UI
    Route::get('/dashboard/messages', [\App\Http\Controllers\Web\MessageWebController::class, 'index'])->name('dashboard.messages.index');
    Route::get('/dashboard/messages/{conversation}', [\App\Http\Controllers\Web\MessageWebController::class, 'show'])->name('dashboard.messages.show');
    Route::post('/dashboard/messages/{conversation}', [\App\Http\Controllers\Web\MessageWebController::class, 'storeMessage'])->name('dashboard.messages.store');
    Route::post('/dashboard/inquiries', [\App\Http\Controllers\Web\MessageWebController::class, 'storeInquiry'])->name('dashboard.inquiries.store');

    // Phase 17: Property Viewing UI
    Route::get('/dashboard/viewings', [\App\Http\Controllers\Web\ViewingWebController::class, 'index'])->name('dashboard.viewings.index');
    Route::get('/dashboard/viewings/create', [\App\Http\Controllers\Web\ViewingWebController::class, 'create'])->name('dashboard.viewings.create');
    Route::post('/dashboard/viewings', [\App\Http\Controllers\Web\ViewingWebController::class, 'store'])->name('dashboard.viewings.store');
    Route::get('/dashboard/viewings/{viewing}', [\App\Http\Controllers\Web\ViewingWebController::class, 'show'])->name('dashboard.viewings.show');
    Route::put('/dashboard/viewings/{viewing}', [\App\Http\Controllers\Web\ViewingWebController::class, 'update'])->name('dashboard.viewings.update');

    // Phase 18: Offers & Negotiation UI
    Route::get('/dashboard/offers', [\App\Http\Controllers\Web\OfferWebController::class, 'index'])->name('dashboard.offers.index');
    Route::get('/dashboard/offers/create', [\App\Http\Controllers\Web\OfferWebController::class, 'create'])->name('dashboard.offers.create');
    Route::post('/dashboard/offers', [\App\Http\Controllers\Web\OfferWebController::class, 'store'])->name('dashboard.offers.store');
    Route::get('/dashboard/offers/{offer}', [\App\Http\Controllers\Web\OfferWebController::class, 'show'])->name('dashboard.offers.show');
    Route::put('/dashboard/offers/{offer}', [\App\Http\Controllers\Web\OfferWebController::class, 'update'])->name('dashboard.offers.update');

    // Phase 19: Marketplace Reservations UI
    Route::get('/dashboard/reservations', [\App\Http\Controllers\Web\ReservationWebController::class, 'index'])->name('dashboard.reservations.index');
    Route::get('/dashboard/reservations/create', [\App\Http\Controllers\Web\ReservationWebController::class, 'create'])->name('dashboard.reservations.create');
    Route::post('/dashboard/reservations', [\App\Http\Controllers\Web\ReservationWebController::class, 'store'])->name('dashboard.reservations.store');
    Route::get('/dashboard/reservations/{reservation}', [\App\Http\Controllers\Web\ReservationWebController::class, 'show'])->name('dashboard.reservations.show');
    Route::put('/dashboard/reservations/{reservation}', [\App\Http\Controllers\Web\ReservationWebController::class, 'update'])->name('dashboard.reservations.update');

    // Phase 20: Accommodation & Host UI
    Route::resource('/dashboard/accommodations', \App\Http\Controllers\Web\AccommodationWebController::class)->names('dashboard.accommodations')->except(['destroy']);

    // Phase 21: Availability & Calendar UI
    Route::get('/dashboard/accommodations/{accommodation}/calendar', [\App\Http\Controllers\Web\AccommodationCalendarWebController::class, 'show'])->name('dashboard.accommodations.calendar');
    Route::post('/dashboard/accommodations/{accommodation}/calendar/block', [\App\Http\Controllers\Web\AccommodationCalendarWebController::class, 'block'])->name('dashboard.accommodations.calendar.block');
    Route::delete('/dashboard/accommodations/{accommodation}/calendar/unblock/{availability}', [\App\Http\Controllers\Web\AccommodationCalendarWebController::class, 'unblock'])->name('dashboard.accommodations.calendar.unblock');

    // Phase 22: Stay Pricing & Cancellation UI
    Route::get('/dashboard/accommodations/{accommodation}/pricing', [\App\Http\Controllers\Web\AccommodationPricingWebController::class, 'edit'])->name('dashboard.accommodations.pricing');
    Route::put('/dashboard/accommodations/{accommodation}/pricing', [\App\Http\Controllers\Web\AccommodationPricingWebController::class, 'update'])->name('dashboard.accommodations.pricing.update');
    Route::post('/dashboard/accommodations/{accommodation}/pricing/rules', [\App\Http\Controllers\Web\AccommodationPricingWebController::class, 'storeRule'])->name('dashboard.accommodations.pricing.rules.store');
    Route::delete('/dashboard/accommodations/{accommodation}/pricing/rules/{rule}', [\App\Http\Controllers\Web\AccommodationPricingWebController::class, 'destroyRule'])->name('dashboard.accommodations.pricing.rules.destroy');

    // Phase 23: Booking Engine UI
    Route::get('/accommodations/{accommodation}/book', [\App\Http\Controllers\Web\BookingEngineWebController::class, 'create'])->name('booking.create');
    Route::post('/accommodations/{accommodation}/book', [\App\Http\Controllers\Web\BookingEngineWebController::class, 'store'])->name('booking.store');
    Route::get('/bookings/{booking}', [\App\Http\Controllers\Web\BookingEngineWebController::class, 'show'])->name('booking.show');
    Route::post('/bookings/{booking}/confirm', [\App\Http\Controllers\Web\BookingEngineWebController::class, 'confirm'])->name('booking.confirm');
    Route::post('/bookings/{booking}/cancel', [\App\Http\Controllers\Web\BookingEngineWebController::class, 'cancel'])->name('booking.cancel');
    Route::post('/bookings/{booking}/check-in', [\App\Http\Controllers\Web\BookingEngineWebController::class, 'checkIn'])->name('booking.check-in');
    Route::post('/bookings/{booking}/check-out', [\App\Http\Controllers\Web\BookingEngineWebController::class, 'checkOut'])->name('booking.check-out');

    // Phase 24: Reviews UI
    Route::get('/bookings/{booking}/review', [\App\Http\Controllers\Web\ReviewWebController::class, 'create'])->name('booking.review.create');
    Route::post('/bookings/{booking}/review', [\App\Http\Controllers\Web\ReviewWebController::class, 'store'])->name('booking.review.store');
    Route::get('/dashboard/reviews', [\App\Http\Controllers\Web\ReviewWebController::class, 'index'])->name('dashboard.reviews.index');

    // Phase 25: Notifications & Automation UI
    Route::get('/dashboard/notifications', [\App\Http\Controllers\Web\NotificationWebController::class, 'index'])->name('dashboard.notifications.index');
    Route::patch('/dashboard/notifications/{id}/read', [\App\Http\Controllers\Web\NotificationWebController::class, 'markAsRead'])->name('dashboard.notifications.read');
    Route::get('/dashboard/preferences', [\App\Http\Controllers\Web\NotificationWebController::class, 'preferences'])->name('dashboard.preferences');
    Route::put('/dashboard/preferences', [\App\Http\Controllers\Web\NotificationWebController::class, 'updatePreferences'])->name('dashboard.preferences.update');

    // Phase 26: Companies, Branches & Agents UI
    Route::get('/dashboard/organization', [\App\Http\Controllers\Web\OrganizationWebController::class, 'index'])->name('dashboard.organization.index');
    Route::get('/dashboard/organization/create', [\App\Http\Controllers\Web\OrganizationWebController::class, 'create'])->name('dashboard.organization.create');
    Route::post('/dashboard/organization', [\App\Http\Controllers\Web\OrganizationWebController::class, 'store'])->name('dashboard.organization.store');
    Route::get('/dashboard/organization/profile', [\App\Http\Controllers\Web\OrganizationWebController::class, 'profile'])->name('dashboard.organization.profile');
    Route::get('/dashboard/organization/branches', [\App\Http\Controllers\Web\OrganizationWebController::class, 'branches'])->name('dashboard.organization.branches');
    Route::get('/dashboard/organization/teams', [\App\Http\Controllers\Web\OrganizationWebController::class, 'teams'])->name('dashboard.organization.teams');
    Route::get('/dashboard/organization/members', [\App\Http\Controllers\Web\OrganizationWebController::class, 'members'])->name('dashboard.organization.members');

    // Phase 27: CRM UI
    Route::get('/dashboard/crm', [\App\Http\Controllers\Web\CrmWebController::class, 'index'])->name('dashboard.crm.index');
    Route::get('/dashboard/crm/leads', [\App\Http\Controllers\Web\CrmWebController::class, 'leads'])->name('dashboard.crm.leads');
    Route::get('/dashboard/crm/leads/{lead}', [\App\Http\Controllers\Web\CrmWebController::class, 'leadShow'])->name('dashboard.crm.leads.show');
    Route::get('/dashboard/crm/pipeline', [\App\Http\Controllers\Web\CrmWebController::class, 'pipeline'])->name('dashboard.crm.pipeline');
    Route::get('/dashboard/crm/clients', [\App\Http\Controllers\Web\CrmWebController::class, 'clients'])->name('dashboard.crm.clients');
    Route::get('/dashboard/crm/tasks', [\App\Http\Controllers\Web\CrmWebController::class, 'tasks'])->name('dashboard.crm.tasks');
    Route::get('/dashboard/crm/commissions', [\App\Http\Controllers\Web\CrmWebController::class, 'commissions'])->name('dashboard.crm.commissions');

    // Phase 28: Developer Projects UI (dashboard)
    Route::get('/dashboard/projects', [\App\Http\Controllers\Web\DeveloperProjectWebController::class, 'index'])->name('dashboard.projects.index');
    Route::get('/dashboard/projects/{project}', [\App\Http\Controllers\Web\DeveloperProjectWebController::class, 'show'])->name('dashboard.projects.show');
    Route::get('/dashboard/projects/{project}/buildings', [\App\Http\Controllers\Web\DeveloperProjectWebController::class, 'buildings'])->name('dashboard.projects.buildings');
    Route::get('/dashboard/projects/{project}/units', [\App\Http\Controllers\Web\DeveloperProjectWebController::class, 'units'])->name('dashboard.projects.units');
    Route::get('/dashboard/projects/{project}/progress', [\App\Http\Controllers\Web\DeveloperProjectWebController::class, 'progress'])->name('dashboard.projects.progress');
    Route::get('/dashboard/projects/{project}/documents', [\App\Http\Controllers\Web\DeveloperProjectWebController::class, 'documents'])->name('dashboard.projects.documents');

    // Phase 29: Developer Sales UI
    Route::get('/dashboard/sales', function () {
        return redirect()->route('dashboard.sales.units');
    })->name('dashboard.sales.index');
    Route::get('/dashboard/sales/units', [\App\Http\Controllers\Web\DeveloperSalesWebController::class, 'units'])->name('dashboard.sales.units');
    Route::get('/dashboard/sales/units/{id}', [\App\Http\Controllers\Web\DeveloperSalesWebController::class, 'unitDetail'])->name('dashboard.sales.units.show');
    Route::get('/dashboard/sales/units/{id}/reservation', [\App\Http\Controllers\Web\DeveloperSalesWebController::class, 'reservation'])->name('dashboard.sales.reservation');
    Route::get('/dashboard/sales/buyers', [\App\Http\Controllers\Web\DeveloperSalesWebController::class, 'buyers'])->name('dashboard.sales.buyers');
    Route::get('/dashboard/sales/buyers/{id}', [\App\Http\Controllers\Web\DeveloperSalesWebController::class, 'buyerDetail'])->name('dashboard.sales.buyers.show');
    Route::get('/dashboard/sales/payment-plans', [\App\Http\Controllers\Web\DeveloperSalesWebController::class, 'paymentPlans'])->name('dashboard.sales.payment_plans');
    Route::get('/dashboard/sales/contracts', [\App\Http\Controllers\Web\DeveloperSalesWebController::class, 'contracts'])->name('dashboard.sales.contracts');
    Route::get('/dashboard/sales/handovers', [\App\Http\Controllers\Web\DeveloperSalesWebController::class, 'handovers'])->name('dashboard.sales.handovers');

    // Transactions & Deals Pipeline UI
    Route::get('/dashboard/transactions', [\App\Http\Controllers\Web\TransactionWebController::class, 'index'])->name('dashboard.transactions');
    Route::get('/dashboard/transactions/{id}', [\App\Http\Controllers\Web\TransactionWebController::class, 'show'])->name('dashboard.transactions.show');

    // Phase 30: Rental & Lease Management UI
    Route::get('/dashboard/rental', [\App\Http\Controllers\Web\RentalWebController::class, 'index'])->name('dashboard.rental.index');
    Route::get('/dashboard/rental/applications', [\App\Http\Controllers\Web\RentalWebController::class, 'applications'])->name('dashboard.rental.applications');
    Route::get('/dashboard/rental/tenants', [\App\Http\Controllers\Web\RentalWebController::class, 'tenants'])->name('dashboard.rental.tenants');
    Route::get('/dashboard/rental/tenants/{id}', [\App\Http\Controllers\Web\RentalWebController::class, 'tenantShow'])->name('dashboard.rental.tenants.show');
    Route::get('/dashboard/rental/leases', [\App\Http\Controllers\Web\RentalWebController::class, 'leases'])->name('dashboard.rental.leases');
    Route::get('/dashboard/rental/leases/{id}', [\App\Http\Controllers\Web\RentalWebController::class, 'leaseShow'])->name('dashboard.rental.leases.show');
    Route::get('/dashboard/rental/payments', [\App\Http\Controllers\Web\RentalWebController::class, 'payments'])->name('dashboard.rental.payments');

    // Phase 31: Operations UI
    Route::get('/dashboard/operations', [\App\Http\Controllers\Web\OperationsWebController::class, 'index'])->name('dashboard.operations.index');
    Route::get('/dashboard/operations/tickets', [\App\Http\Controllers\Web\OperationsWebController::class, 'tickets'])->name('dashboard.operations.tickets');
    Route::get('/dashboard/operations/tickets/{id}', [\App\Http\Controllers\Web\OperationsWebController::class, 'ticketShow'])->name('dashboard.operations.tickets.show');
    Route::get('/dashboard/operations/cleaning', [\App\Http\Controllers\Web\OperationsWebController::class, 'cleaning'])->name('dashboard.operations.cleaning');
    Route::get('/dashboard/operations/staff', [\App\Http\Controllers\Web\OperationsWebController::class, 'staff'])->name('dashboard.operations.staff');

    // Phase 32: Finance UI
    Route::get('/dashboard/finance', [\App\Http\Controllers\Web\FinancialWebController::class, 'index'])->name('dashboard.finance.index');
    Route::get('/dashboard/finance/transactions', [\App\Http\Controllers\Web\FinancialWebController::class, 'transactions'])->name('dashboard.finance.transactions');
    Route::get('/dashboard/finance/transactions/{id}', [\App\Http\Controllers\Web\FinancialWebController::class, 'transactionShow'])->name('dashboard.finance.transactions.show');
    Route::get('/dashboard/finance/invoices', [\App\Http\Controllers\Web\FinancialWebController::class, 'invoices'])->name('dashboard.finance.invoices');
    Route::get('/dashboard/finance/ledger', [\App\Http\Controllers\Web\FinancialWebController::class, 'ledger'])->name('dashboard.finance.ledger');

    // Phase 33: Documents & Verification UI
    Route::get('/dashboard/documents', [\App\Http\Controllers\Web\DocumentWebController::class, 'index'])->name('dashboard.documents.index');
    Route::get('/dashboard/documents/files/{id}', [\App\Http\Controllers\Web\DocumentWebController::class, 'documentShow'])->name('dashboard.documents.show');
    Route::get('/dashboard/documents/verifications', [\App\Http\Controllers\Web\DocumentWebController::class, 'verifications'])->name('dashboard.documents.verifications');
    Route::get('/dashboard/documents/verifications/{id}', [\App\Http\Controllers\Web\DocumentWebController::class, 'verificationShow'])->name('dashboard.documents.verifications.show');

    // Phase 34: Fraud, Reporting & Disputes UI
    Route::get('/dashboard/trust', [\App\Http\Controllers\Web\TrustWebController::class, 'index'])->name('dashboard.trust.index');
    Route::get('/dashboard/trust/reports', [\App\Http\Controllers\Web\TrustWebController::class, 'reports'])->name('dashboard.trust.reports');
    Route::get('/dashboard/trust/disputes', [\App\Http\Controllers\Web\TrustWebController::class, 'disputes'])->name('dashboard.trust.disputes');
    Route::get('/dashboard/trust/cases/{id}', [\App\Http\Controllers\Web\TrustWebController::class, 'caseShow'])->name('dashboard.trust.cases.show');

    // Phase 35: Administration Platform UI (Restricted to Administrator role)
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/dashboard/admin', [\App\Http\Controllers\Web\AdminWebController::class, 'index'])->name('dashboard.admin.index');
        Route::get('/dashboard/admin/users', [\App\Http\Controllers\Web\AdminWebController::class, 'users'])->name('dashboard.admin.users');
        Route::get('/dashboard/admin/users/create', [\App\Http\Controllers\Web\AdminWebController::class, 'userCreate'])->name('dashboard.admin.users.create');
        Route::post('/dashboard/admin/users', [\App\Http\Controllers\Web\AdminWebController::class, 'userStore'])->name('dashboard.admin.users.store');
        Route::post('/dashboard/admin/users/{id}/role', [\App\Http\Controllers\Web\AdminWebController::class, 'updateUserRole'])->name('dashboard.admin.users.role');
        Route::get('/dashboard/users', [\App\Http\Controllers\Web\AdminWebController::class, 'users'])->name('dashboard.users');
        Route::get('/dashboard/users/create', [\App\Http\Controllers\Web\AdminWebController::class, 'userCreate'])->name('dashboard.users.create');
        Route::post('/dashboard/users', [\App\Http\Controllers\Web\AdminWebController::class, 'userStore'])->name('dashboard.users.store');
        Route::get('/dashboard/admin/users/{id}', [\App\Http\Controllers\Web\AdminWebController::class, 'userShow'])->name('dashboard.admin.users.show');
        Route::get('/dashboard/admin/permissions', [\App\Http\Controllers\Web\AdminWebController::class, 'permissions'])->name('dashboard.admin.permissions');
        Route::post('/dashboard/admin/permissions', [\App\Http\Controllers\Web\AdminWebController::class, 'updatePermissions'])->name('dashboard.admin.permissions.update');
        Route::get('/dashboard/admin/audit', [\App\Http\Controllers\Web\AdminWebController::class, 'audit'])->name('dashboard.admin.audit');
        Route::get('/dashboard/admin/settings', [\App\Http\Controllers\Web\AdminWebController::class, 'settings'])->name('dashboard.admin.settings');
    });

    // Account & Platform Settings
    Route::get('/dashboard/settings', [\App\Http\Controllers\Web\SettingsWebController::class, 'index'])->name('dashboard.settings');
    Route::post('/dashboard/settings/profile', [\App\Http\Controllers\Web\SettingsWebController::class, 'updateProfile'])->name('dashboard.settings.profile');
    Route::post('/dashboard/settings/security', [\App\Http\Controllers\Web\SettingsWebController::class, 'updateSecurity'])->name('dashboard.settings.security');
    Route::post('/dashboard/settings/notifications', [\App\Http\Controllers\Web\SettingsWebController::class, 'updateNotifications'])->name('dashboard.settings.notifications');
    Route::post('/dashboard/settings/platform', [\App\Http\Controllers\Web\SettingsWebController::class, 'updatePlatform'])->name('dashboard.settings.platform');
    Route::get('/dashboard/profile', function () {
        return redirect()->route('dashboard.settings', ['tab' => 'profile']);
    })->name('dashboard.profile');

    // Phase 36: Reporting & Analytics UI
    Route::get('/dashboard/analytics', [\App\Http\Controllers\Web\AnalyticsWebController::class, 'index'])->name('dashboard.analytics.index');
    Route::get('/dashboard/analytics/reports', [\App\Http\Controllers\Web\AnalyticsWebController::class, 'reports'])->name('dashboard.analytics.reports');
    Route::get('/dashboard/analytics/exports', [\App\Http\Controllers\Web\AnalyticsWebController::class, 'exports'])->name('dashboard.analytics.exports');

    // Phase 37: Search Alerts & Recommendations UI
    Route::get('/dashboard/alerts', [\App\Http\Controllers\Web\AlertsWebController::class, 'index'])->name('dashboard.alerts.index');
    Route::get('/dashboard/alerts/{id}', [\App\Http\Controllers\Web\AlertsWebController::class, 'show'])->name('dashboard.alerts.show');
    Route::get('/dashboard/recommendations', [\App\Http\Controllers\Web\AlertsWebController::class, 'recommendations'])->name('dashboard.recommendations.index');

    // Phase 39: Advanced GIS UI
    Route::get('/dashboard/map', [\App\Http\Controllers\Web\GisWebController::class, 'index'])->name('dashboard.gis.index');

    // Phase 41: Valuation & Investment Intelligence UI
    Route::get('/dashboard/intelligence/valuation', [\App\Http\Controllers\Web\IntelligenceWebController::class, 'valuation'])->name('dashboard.intelligence.valuation');
    Route::get('/dashboard/intelligence/calculators', [\App\Http\Controllers\Web\IntelligenceWebController::class, 'calculators'])->name('dashboard.intelligence.calculators');
    Route::get('/dashboard/intelligence/portfolio', [\App\Http\Controllers\Web\IntelligenceWebController::class, 'portfolio'])->name('dashboard.intelligence.portfolio');

    // Phase 42: Professional Services Marketplace UI (dashboard)
    Route::get('/dashboard/services', [\App\Http\Controllers\Web\ServicesWebController::class, 'index'])->name('dashboard.services.index');
    Route::get('/dashboard/services/provider', [\App\Http\Controllers\Web\ServicesWebController::class, 'dashboard'])->name('dashboard.services.dashboard');
    Route::get('/dashboard/services/{id}', [\App\Http\Controllers\Web\ServicesWebController::class, 'show'])->name('dashboard.services.show');

    // Phase 43: Advanced Integrations UI
    Route::get('/dashboard/integrations', [\App\Http\Controllers\Web\IntegrationsWebController::class, 'index'])->name('dashboard.integrations.index');
    Route::get('/dashboard/integrations/history', [\App\Http\Controllers\Web\IntegrationsWebController::class, 'history'])->name('dashboard.integrations.history');

    // Phase 40: AI Search & Listing Assistant UI
    Route::get('/dashboard/ai/search', [\App\Http\Controllers\Web\AiSearchWebController::class, 'search'])->name('dashboard.ai.search');
    Route::match(['get', 'post'], '/dashboard/ai/assistant', [\App\Http\Controllers\Web\AiSearchWebController::class, 'assistant'])->name('dashboard.ai.assistant');

    // Dashboard properties (PropertyWebController)
    Route::get('/dashboard/properties', [\App\Http\Controllers\Web\PropertyWebController::class, 'index'])->name('dashboard.properties.index');
    Route::get('/dashboard/properties/create', [\App\Http\Controllers\Web\PropertyWebController::class, 'create'])->name('dashboard.properties.create');
    Route::post('/dashboard/properties', [\App\Http\Controllers\Web\PropertyWebController::class, 'store'])->name('dashboard.properties.store');
    Route::get('/dashboard/properties/{property}/edit', [\App\Http\Controllers\Web\PropertyWebController::class, 'edit'])->name('dashboard.properties.edit');
    Route::put('/dashboard/properties/{property}', [\App\Http\Controllers\Web\PropertyWebController::class, 'update'])->name('dashboard.properties.update');
    Route::delete('/dashboard/properties/{property}', [\App\Http\Controllers\Web\PropertyWebController::class, 'destroy'])->name('dashboard.properties.destroy');

    // Phase 11: Listing Core UI
    Route::get('/dashboard/listings', [\App\Http\Controllers\Web\ListingController::class, 'index'])->name('dashboard.listings.index');
    Route::get('/dashboard/listings/create', [\App\Http\Controllers\Web\ListingController::class, 'create'])->name('dashboard.listings.create');
    Route::post('/dashboard/listings', [\App\Http\Controllers\Web\ListingController::class, 'store'])->name('dashboard.listings.store');
    Route::get('/dashboard/listings/{listing}/edit', [\App\Http\Controllers\Web\ListingController::class, 'edit'])->name('dashboard.listings.edit');
    Route::put('/dashboard/listings/{listing}', [\App\Http\Controllers\Web\ListingController::class, 'update'])->name('dashboard.listings.update');
    Route::patch('/dashboard/listings/{listing}/toggle-status', [\App\Http\Controllers\Web\ListingController::class, 'toggleStatus'])->name('dashboard.listings.toggle-status');
    Route::delete('/dashboard/listings/{listing}', [\App\Http\Controllers\Web\ListingController::class, 'destroy'])->name('dashboard.listings.destroy');
    Route::get('/dashboard/listings/{listing}/preview', [\App\Http\Controllers\Web\ListingController::class, 'preview'])->name('dashboard.listings.preview');

    // Phase 12: Listing Submission & Lifecycle Status (User Screens)
    Route::get('/dashboard/listings/{listing}/submit', [\App\Http\Controllers\Web\ListingSubmissionWebController::class, 'showSubmitForm'])->name('dashboard.listings.submit');
    Route::post('/dashboard/listings/{listing}/submit', [\App\Http\Controllers\Web\ListingSubmissionWebController::class, 'processSubmit'])->name('dashboard.listings.submit.process');
    Route::get('/dashboard/listings/{listing}/submission-status', [\App\Http\Controllers\Web\ListingSubmissionWebController::class, 'status'])->name('dashboard.listings.submission-status');

    // Phase 12: Listing Moderation Queue & Decision Engine (Moderator Screens)
    Route::get('/dashboard/moderation/listings', [\App\Http\Controllers\Web\ListingModerationWebController::class, 'index'])->name('dashboard.moderation.listings.index');
    Route::get('/dashboard/moderation/listings/{listing}', [\App\Http\Controllers\Web\ListingModerationWebController::class, 'show'])->name('dashboard.moderation.listings.show');
    Route::post('/dashboard/moderation/listings/{listing}/decision', [\App\Http\Controllers\Web\ListingModerationWebController::class, 'decision'])->name('dashboard.moderation.listings.decision');

    // Phase 08: Property Features & Amenities Catalog Management
    Route::get('/dashboard/amenities', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'index'])->name('dashboard.amenities.index');
    Route::post('/dashboard/amenities', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'store'])->name('dashboard.amenities.store');
    Route::put('/dashboard/amenities/{amenity}', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'update'])->name('dashboard.amenities.update');
    Route::patch('/dashboard/amenities/{amenity}/toggle-status', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'toggleStatus'])->name('dashboard.amenities.toggle');
    Route::delete('/dashboard/amenities/{amenity}', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'destroy'])->name('dashboard.amenities.destroy');

    Route::get('/dashboard/features', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'index'])->name('dashboard.features.index');
    Route::post('/dashboard/features', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'store'])->name('dashboard.features.store');
    Route::put('/dashboard/features/{feature}', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'update'])->name('dashboard.features.update');
    Route::patch('/dashboard/features/{feature}/toggle-status', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'toggleStatus'])->name('dashboard.features.toggle');
    Route::delete('/dashboard/features/{feature}', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'destroy'])->name('dashboard.features.destroy');

    // Phase 09: Property Media UI
    Route::get('/dashboard/properties/media', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'index'])->name('dashboard.properties.media.demo');
    Route::get('/dashboard/properties/{property}/media', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'index'])->name('dashboard.properties.media');
    Route::post('/dashboard/properties/{property}/media', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'store'])->name('dashboard.properties.media.store');
    Route::put('/dashboard/properties/{property}/media/order', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'updateOrder'])->name('dashboard.properties.media.order');
    Route::patch('/dashboard/properties/{property}/media/{media}/set-primary', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'setPrimary'])->name('dashboard.properties.media.primary');
    Route::delete('/dashboard/properties/{property}/media/{media}', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'destroy'])->name('dashboard.properties.media.destroy');

    // Phase 10: GIS Foundation UI
    Route::get('/dashboard/properties/map', function () {
        return view('dashboard.properties.map');
    })->name('dashboard.properties.map');
});
