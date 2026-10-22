<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Api\V1\OfferController as DomainOfferController;

class_alias(DomainOfferController::class, 'App\Http\Controllers\Api\V1\OfferController');
