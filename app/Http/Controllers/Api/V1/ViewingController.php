<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Api\V1\ViewingController as DomainViewingController;

class_alias(DomainViewingController::class, 'App\Http\Controllers\Api\V1\ViewingController');
