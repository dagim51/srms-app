<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Administration\Http\Controllers\Api\V1\WebhookController as DomainWebhookController;

class_alias(DomainWebhookController::class, 'App\Http\Controllers\Api\V1\WebhookController');
