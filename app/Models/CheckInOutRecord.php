<?php

namespace App\Models;

use App\Domains\CheckInOut\Models\CheckInOutRecord as DomainCheckInOutRecord;

class_alias(DomainCheckInOutRecord::class, 'App\Models\CheckInOutRecord');
