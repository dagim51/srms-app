<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blueprint::macro('auditColumns', function () {
            $this->uuid('created_by')->nullable()->index();
            $this->uuid('updated_by')->nullable()->index();
            $this->uuid('deleted_by')->nullable()->index();
        });

        Blueprint::macro('statusColumn', function ($default = 'active') {
            $this->string('status', 50)->default($default)->index();
        });
    }
}
