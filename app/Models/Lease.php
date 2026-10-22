<?php

namespace App\Models;

use App\Domains\Rental\Models\Lease as DomainLease;

class_alias(DomainLease::class, 'App\Models\Lease');
