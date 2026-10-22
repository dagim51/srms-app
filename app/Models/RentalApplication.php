<?php

namespace App\Models;

use App\Domains\Rental\Models\RentalApplication as DomainRentalApplication;

class_alias(DomainRentalApplication::class, 'App\Models\RentalApplication');
