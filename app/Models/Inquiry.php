<?php

namespace App\Models;

use App\Domains\Engagement\Models\Inquiry as DomainInquiry;

class_alias(DomainInquiry::class, 'App\Models\Inquiry');
