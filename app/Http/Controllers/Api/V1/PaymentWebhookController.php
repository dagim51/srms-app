<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Transaction\Http\Controllers\Api\V1\PaymentWebhookController as DomainPaymentWebhookController;

class_alias(DomainPaymentWebhookController::class, 'App\Http\Controllers\Api\V1\PaymentWebhookController');
