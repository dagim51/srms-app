<?php

namespace App\Http\Controllers\Web;

use App\Domains\Notification\Http\Controllers\Web\NotificationWebController as DomainNotificationWebController;

class_alias(DomainNotificationWebController::class, 'App\Http\Controllers\Web\NotificationWebController');
