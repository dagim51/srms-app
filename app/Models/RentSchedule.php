<?php

namespace App\Models;

use App\Domains\Rental\Models\RentSchedule as DomainRentSchedule;

class_alias(DomainRentSchedule::class, 'App\Models\RentSchedule');
