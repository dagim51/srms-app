<?php

namespace App\Models;

use App\Domains\Rental\Models\TenantProfile as DomainTenantProfile;

class_alias(DomainTenantProfile::class, 'App\Models\TenantProfile');
