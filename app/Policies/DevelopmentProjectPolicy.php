<?php

namespace App\Policies;

use App\Domains\Development\Policies\DevelopmentProjectPolicy as DomainDevelopmentProjectPolicy;

class_alias(DomainDevelopmentProjectPolicy::class, 'App\Policies\DevelopmentProjectPolicy');
