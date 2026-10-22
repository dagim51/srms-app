<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Transaction\Http\Controllers\Api\V1\PaymentController as DomainPaymentController;

class_alias(DomainPaymentController::class, 'App\Http\Controllers\Api\V1\PaymentController');
