<?php

namespace App\Models;

use App\Domains\Stay\Models\Accommodation as DomainAccommodation;

class_alias(DomainAccommodation::class, 'App\Models\Accommodation');
