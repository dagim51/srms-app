<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Review\Http\Controllers\Api\V1\ReviewController as DomainReviewController;

class_alias(DomainReviewController::class, 'App\Http\Controllers\Api\V1\ReviewController');
