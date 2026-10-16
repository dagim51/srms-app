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
    }
}
