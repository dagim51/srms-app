<?php

namespace App\Models;

use App\Domains\CRM\Models\Lead as DomainLead;

class_alias(DomainLead::class, 'App\Models\Lead');
