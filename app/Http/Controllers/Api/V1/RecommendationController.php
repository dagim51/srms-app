<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Analytics\Http\Controllers\Api\V1\RecommendationController as DomainRecommendationController;

class_alias(DomainRecommendationController::class, 'App\Http\Controllers\Api\V1\RecommendationController');
