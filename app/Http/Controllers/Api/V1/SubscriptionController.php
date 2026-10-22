<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Subscription\Http\Controllers\Api\V1\SubscriptionController as DomainSubscriptionController;

class_alias(DomainSubscriptionController::class, 'App\Http\Controllers\Api\V1\SubscriptionController');
