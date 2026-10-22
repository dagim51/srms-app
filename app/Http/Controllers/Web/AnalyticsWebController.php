<?php

namespace App\Http\Controllers\Web;

use App\Domains\Analytics\Http\Controllers\Web\AnalyticsWebController as DomainAnalyticsWebController;

class_alias(DomainAnalyticsWebController::class, 'App\Http\Controllers\Web\AnalyticsWebController');
