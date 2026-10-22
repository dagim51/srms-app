<?php

namespace App\Models;

use App\Domains\PropertyManagement\Models\Community as DomainCommunity;

class_alias(DomainCommunity::class, 'App\Models\Community');
