<?php

namespace App\Models;

use App\Domains\Property\Models\PropertyStatusHistory as DomainPropertyStatusHistory;

class_alias(DomainPropertyStatusHistory::class, 'App\Models\PropertyStatusHistory');
