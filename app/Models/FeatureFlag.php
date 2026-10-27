<?php

namespace App\Models;

use App\Domains\Administration\Models\FeatureFlag as DomainFeatureFlag;

class_alias(DomainFeatureFlag::class, 'App\Models\FeatureFlag');
