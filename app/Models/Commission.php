<?php

namespace App\Models;

use App\Domains\CRM\Models\Commission as DomainCommission;

class_alias(DomainCommission::class, 'App\Models\Commission');
