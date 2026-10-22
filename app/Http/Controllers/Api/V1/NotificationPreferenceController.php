<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Notification\Http\Controllers\Api\V1\NotificationPreferenceController as DomainNotificationPreferenceController;

class_alias(DomainNotificationPreferenceController::class, 'App\Http\Controllers\Api\V1\NotificationPreferenceController');
