<?php

namespace App\Policies;

use App\Domains\Rental\Policies\LeasePolicy as DomainLeasePolicy;

class_alias(DomainLeasePolicy::class, 'App\Policies\LeasePolicy');
