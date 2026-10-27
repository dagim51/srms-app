<?php

namespace App\Models;

use App\Domains\Administration\Models\SystemSetting as DomainSystemSetting;

class_alias(DomainSystemSetting::class, 'App\Models\SystemSetting');
