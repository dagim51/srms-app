<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Api\V1\ConversationController as DomainConversationController;

class_alias(DomainConversationController::class, 'App\Http\Controllers\Api\V1\ConversationController');
