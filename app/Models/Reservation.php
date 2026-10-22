<?php

namespace App\Models;

use App\Domains\Stay\Models\Reservation as DomainReservation;

class_alias(DomainReservation::class, 'App\Models\Reservation');
