<?php

namespace App\Models;

use App\Domains\Stay\Models\ExternalCalendarSync as DomainExternalCalendarSync;

class_alias(DomainExternalCalendarSync::class, 'App\Models\ExternalCalendarSync');
