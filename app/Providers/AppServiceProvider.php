<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Events\Bookings\BookingCancelled;
use App\Events\Bookings\BookingConfirmed;
use App\Events\Bookings\BookingCreated;
use App\Events\Inquiries\InquiryCreated;
use App\Events\Inquiries\InquiryResponded;
use App\Events\Listings\ListingPublished;
use App\Events\Listings\ListingUnpublished;
use App\Events\Payments\PaymentCompleted;
use App\Events\Payments\PaymentFailed;
use App\Events\Payments\PaymentRefunded;
use App\Listeners\BookingCancelledListener;
use App\Listeners\BookingConfirmedListener;
use App\Listeners\BookingCreatedListener;
use App\Listeners\InquiryCreatedListener;
use App\Listeners\InquiryRespondedListener;
use App\Listeners\ListingPublishedListener;
use App\Listeners\ListingUnpublishedListener;
use App\Listeners\PaymentCompletedListener;
use App\Listeners\PaymentFailedListener;
use App\Listeners\PaymentRefundedListener;
use App\Services\Payments\TelebirrGateway;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, TelebirrGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(200)->by($request->ip());
        });

        Blueprint::macro('auditColumns', function () {
            $this->uuid('created_by')->nullable()->index();
            $this->uuid('updated_by')->nullable()->index();
            $this->uuid('deleted_by')->nullable()->index();
        });

        Blueprint::macro('statusColumn', function ($default = 'active') {
            $this->string('status', 50)->default($default)->index();
        });

        // Register event listeners
        Event::listen(ListingPublished::class, ListingPublishedListener::class);
        Event::listen(ListingUnpublished::class, ListingUnpublishedListener::class);
        Event::listen(BookingCreated::class, BookingCreatedListener::class);
        Event::listen(BookingConfirmed::class, BookingConfirmedListener::class);
        Event::listen(BookingCancelled::class, BookingCancelledListener::class);
        Event::listen(InquiryCreated::class, InquiryCreatedListener::class);
        Event::listen(InquiryResponded::class, InquiryRespondedListener::class);
        Event::listen(PaymentCompleted::class, PaymentCompletedListener::class);
        Event::listen(PaymentFailed::class, PaymentFailedListener::class);
        Event::listen(PaymentRefunded::class, PaymentRefundedListener::class);
        Event::listen(\App\Events\Rentals\LeaseActivated::class, \App\Listeners\LeaseActivatedListener::class);
        Event::listen(\App\Events\Offers\OfferSubmitted::class, \App\Listeners\OfferNegotiationListener::class);
        Event::listen(\App\Events\Offers\OfferAccepted::class, \App\Listeners\OfferNegotiationListener::class);
        Event::listen(\App\Events\Offers\OfferRejected::class, \App\Listeners\OfferNegotiationListener::class);
        Event::listen(\App\Events\Offers\OfferWithdrawn::class, \App\Listeners\OfferNegotiationListener::class);
        Event::listen(\App\Events\Offers\OfferCountered::class, \App\Listeners\OfferNegotiationListener::class);
        Event::listen(\App\Events\Development\ProjectMilestoneCompleted::class, \App\Listeners\DevelopmentProjectListener::class);
        Event::listen(\App\Events\Development\DevelopmentUnitReserved::class, \App\Listeners\DevelopmentProjectListener::class);
        Event::listen(\App\Events\Operations\TicketCreated::class, \App\Listeners\MaintenanceTicketListener::class);
        Event::listen(\App\Events\Operations\TicketDispatched::class, \App\Listeners\MaintenanceTicketListener::class);
        Event::listen(\App\Events\Operations\TicketResolved::class, \App\Listeners\MaintenanceTicketListener::class);
        Event::listen(\App\Events\Listings\ListingApproved::class, \App\Listeners\ListingModerationListener::class);
        Event::listen(\App\Events\Listings\ListingRejected::class, \App\Listeners\ListingModerationListener::class);
        Event::listen(\App\Events\Listings\ListingSuspended::class, \App\Listeners\ListingModerationListener::class);
        Event::listen(\App\Events\Trust\DisputeOpened::class, \App\Listeners\DisputeListener::class);
        Event::listen(\App\Events\Trust\DisputeResolved::class, \App\Listeners\DisputeListener::class);

        // Register Multi-Tenant Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Lease::class, \App\Policies\LeasePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\AccommodationBooking::class, \App\Policies\AccommodationBookingPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\DevelopmentProject::class, \App\Policies\DevelopmentProjectPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Payment::class, \App\Policies\PaymentPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Dispute::class, \App\Policies\DisputePolicy::class);

        // RBAC: Grant Administrator super-admin access across all abilities
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user instanceof \App\Models\User && $user->hasRole('Administrator')) {
                return true;
            }
        });

        // RBAC: Fallback to database-driven permissions if no policy denies it explicitly
        \Illuminate\Support\Facades\Gate::after(function ($user, $ability, $result) {
            if (($result === null || $result === false) && $user instanceof \App\Models\User) {
                return $user->hasPermissionTo($ability) ? true : null;
            }
            return $result;
        });
    }
}
