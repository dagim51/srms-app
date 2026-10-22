<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Development\Http\Controllers\Api\V1\DevelopmentController as DomainDevelopmentController;

class_alias(DomainDevelopmentController::class, 'App\Http\Controllers\Api\V1\DevelopmentController');
