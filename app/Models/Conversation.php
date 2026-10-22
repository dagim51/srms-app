<?php

namespace App\Models;

use App\Domains\Engagement\Models\Conversation as DomainConversation;

class_alias(DomainConversation::class, 'App\Models\Conversation');
