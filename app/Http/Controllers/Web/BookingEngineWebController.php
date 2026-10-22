<?php

namespace App\Http\Controllers\Web;

use App\Domains\Booking\Http\Controllers\Web\BookingEngineWebController as DomainBookingEngineWebController;

class_alias(DomainBookingEngineWebController::class, 'App\Http\Controllers\Web\BookingEngineWebController');
