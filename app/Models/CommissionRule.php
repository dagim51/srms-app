<?php

namespace App\Models;

use App\Domains\CRM\Models\CommissionRule as DomainCommissionRule;

class_alias(DomainCommissionRule::class, 'App\Models\CommissionRule');
