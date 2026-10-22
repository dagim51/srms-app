<?php

namespace App\Models;

use App\Domains\Engagement\Models\Message as DomainMessage;

class_alias(DomainMessage::class, 'App\Models\Message');
