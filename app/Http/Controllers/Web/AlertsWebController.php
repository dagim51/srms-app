<?php

namespace App\Http\Controllers\Web;

use App\Domains\Notification\Http\Controllers\Web\AlertsWebController as DomainAlertsWebController;

class_alias(DomainAlertsWebController::class, 'App\Http\Controllers\Web\AlertsWebController');
