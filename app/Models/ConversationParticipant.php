<?php

namespace App\Models;

use App\Domains\Engagement\Models\ConversationParticipant as DomainConversationParticipant;

class_alias(DomainConversationParticipant::class, 'App\Models\ConversationParticipant');
