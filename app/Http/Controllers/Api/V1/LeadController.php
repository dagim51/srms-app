<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\CRM\Http\Controllers\Api\V1\LeadController as DomainLeadController;

class_alias(DomainLeadController::class, 'App\Http\Controllers\Api\V1\LeadController');
