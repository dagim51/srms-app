<?php

namespace App\Models;

use App\Domains\Identity\Models\TrustedDevice as DomainTrustedDevice;

class_alias(DomainTrustedDevice::class, 'App\Models\TrustedDevice');
