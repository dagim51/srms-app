<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Notification\Http\Controllers\Api\V1\NotificationController as DomainNotificationController;

class_alias(DomainNotificationController::class, 'App\Http\Controllers\Api\V1\NotificationController');
