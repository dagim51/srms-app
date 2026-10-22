<?php

namespace App\Models;

use App\Domains\Engagement\Models\MessageAttachment as DomainMessageAttachment;

class_alias(DomainMessageAttachment::class, 'App\Models\MessageAttachment');
