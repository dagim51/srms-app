<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Api\V1\MessageController as DomainMessageController;

class_alias(DomainMessageController::class, 'App\Http\Controllers\Api\V1\MessageController');
