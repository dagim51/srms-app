<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Events\Bookings\BookingCancelled;
use App\Events\Bookings\BookingConfirmed;
use App\Events\Bookings\BookingCreated;
use App\Events\Development\ConstructionMilestoneCertified;
use App\Events\Development\DevelopmentUnitReserved;
use App\Events\Development\ProjectMilestoneCompleted;
use App\Events\Inquiries\InquiryCreated;
use App\Events\Inquiries\InquiryResponded;
use App\Events\Listings\ListingApproved;
use App\Events\Listings\ListingPublished;
use App\Events\Listings\ListingRejected;
use App\Events\Listings\ListingSuspended;
use App\Events\Listings\ListingUnpublished;
use App\Events\Offers\OfferAccepted;
use App\Events\Offers\OfferCountered;
use App\Events\Offers\OfferRejected;
use App\Events\Offers\OfferSubmitted;
use App\Events\Offers\OfferWithdrawn;
use App\Events\Operations\TicketCreated;
use App\Events\Operations\TicketDispatched;
use App\Events\Operations\TicketResolved;
use App\Events\Payments\PaymentCompleted;
use App\Events\Payments\PaymentFailed;
use App\Events\Payments\PaymentRefunded;
use App\Events\Rentals\LeaseActivated;
use App\Events\Trust\DisputeOpened;
use App\Events\Trust\DisputeResolved;
use App\Listeners\BookingCancelledListener;
use App\Listeners\BookingConfirmedListener;
use App\Listeners\BookingCreatedListener;
use App\Listeners\DevelopmentProjectListener;
use App\Listeners\DisputeListener;
use App\Listeners\Identity\LoginListener;
use App\Listeners\InquiryCreatedListener;
use App\Listeners\InquiryRespondedListener;
use App\Listeners\LeaseActivatedListener;
use App\Listeners\ListingModerationListener;
use App\Listeners\ListingPublishedListener;
use App\Listeners\ListingUnpublishedListener;
use App\Listeners\MaintenanceTicketListener;
use App\Listeners\OfferNegotiationListener;
use App\Listeners\PaymentCompletedListener;
use App\Listeners\PaymentFailedListener;
use App\Listeners\PaymentRefundedListener;
use App\Listeners\TriggerMilestoneInstallmentInvoicing;
use App\Models\AccommodationBooking;
use App\Models\DevelopmentProject;
use App\Models\Dispute;
use App\Models\Document;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Observers\PropertyObserver;
use App\Policies\AccommodationBookingPolicy;
use App\Policies\DevelopmentProjectPolicy;
use App\Policies\DisputePolicy;
use App\Policies\DocumentPolicy;
use App\Policies\LeasePolicy;
use App\Policies\PaymentPolicy;
use App\Services\Payments\TelebirrGateway;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

        // Register Model Observers
        Property::observe(PropertyObserver::class);

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
        Event::listen(LeaseActivated::class, LeaseActivatedListener::class);
        Event::listen(OfferSubmitted::class, OfferNegotiationListener::class);
        Event::listen(OfferAccepted::class, OfferNegotiationListener::class);
        Event::listen(OfferRejected::class, OfferNegotiationListener::class);
        Event::listen(OfferWithdrawn::class, OfferNegotiationListener::class);
        Event::listen(OfferCountered::class, OfferNegotiationListener::class);
        Event::listen(ConstructionMilestoneCertified::class, TriggerMilestoneInstallmentInvoicing::class);
        Event::listen(ProjectMilestoneCompleted::class, DevelopmentProjectListener::class);
        Event::listen(DevelopmentUnitReserved::class, DevelopmentProjectListener::class);
        Event::listen(TicketCreated::class, MaintenanceTicketListener::class);
        Event::listen(TicketDispatched::class, MaintenanceTicketListener::class);
        Event::listen(TicketResolved::class, MaintenanceTicketListener::class);
        Event::listen(ListingApproved::class, ListingModerationListener::class);
        Event::listen(ListingRejected::class, ListingModerationListener::class);
        Event::listen(ListingSuspended::class, ListingModerationListener::class);
        Event::listen(DisputeOpened::class, DisputeListener::class);
        Event::listen(DisputeResolved::class, DisputeListener::class);
        Event::listen(Login::class, [LoginListener::class, 'handleLogin']);
        Event::listen(Failed::class, [LoginListener::class, 'handleFailed']);

        // Register Multi-Tenant Policies
        Gate::policy(Lease::class, LeasePolicy::class);
        Gate::policy(AccommodationBooking::class, AccommodationBookingPolicy::class);
        Gate::policy(DevelopmentProject::class, DevelopmentProjectPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Dispute::class, DisputePolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);

        // RBAC: Grant Administrator super-admin access across all abilities
        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->hasRole('Administrator')) {
                return true;
            }
        });

        // RBAC: Fallback to database-driven permissions if no policy denies it explicitly
        Gate::after(function ($user, $ability, $result) {
            if (($result === null || $result === false) && $user instanceof User) {
                return $user->hasPermissionTo($ability) ? true : null;
            }

            return $result;
        });

        // Share live notifications with dashboard layouts
        View::composer('components.layouts.dashboard', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $unreadCount = $user->unreadNotifications()->count();
                $recentNotifications = $user->notifications()->latest()->take(6)->get();

                // If user has zero notifications, seed sample domain notifications for realistic workflow demo
                if ($recentNotifications->isEmpty() && $user->id) {
                    $user->notifications()->create([
                        'id' => Str::uuid()->toString(),
                        'type' => 'App\Notifications\EscrowMilestoneFunded',
                        'data' => [
                            'title' => 'Escrow Milestone Funded',
                            'message' => 'Buyer deposited 13,000,000 ETB earnest for Contemporary Luxury Villa in Bole Atlas.',
                            'action_url' => '/dashboard/transactions/1',
                            'category' => 'deals',
                        ],
                        'read_at' => null,
                        'created_at' => now()->subMinutes(12),
                    ]);
                    $user->notifications()->create([
                        'id' => Str::uuid()->toString(),
                        'type' => 'App\Notifications\InquiryReceived',
                        'data' => [
                            'title' => 'New Diaspora Inquiry',
                            'message' => 'Dr. Meron Tadesse requested a verified site tour for Bole Atlas Luxury Villa.',
                            'action_url' => '/dashboard/crm',
                            'category' => 'inquiries',
                        ],
                        'read_at' => null,
                        'created_at' => now()->subHours(2),
                    ]);
                    $user->notifications()->create([
                        'id' => Str::uuid()->toString(),
                        'type' => 'App\Notifications\ListingAuditPassed',
                        'data' => [
                            'title' => 'Cadastral Deed Verified',
                            'message' => 'Title Deed Cert #AA-BOL-2026/8941 passed cadastral compliance review.',
                            'action_url' => '/dashboard/properties',
                            'category' => 'deals',
                        ],
                        'read_at' => now()->subDay(),
                        'created_at' => now()->subDays(1),
                    ]);

                    $unreadCount = $user->unreadNotifications()->count();
                    $recentNotifications = $user->notifications()->latest()->take(6)->get();
                }

                $view->with('unreadNotificationsCount', $unreadCount)
                    ->with('recentNotifications', $recentNotifications);
            } else {
                $view->with('unreadNotificationsCount', 0)
                    ->with('recentNotifications', collect());
            }
        });
    }
}
