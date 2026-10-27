<?php

namespace App\Models;

use App\Domains\Development\Models\HandoverRecord as DomainHandoverRecord;

class_alias(DomainHandoverRecord::class, 'App\Models\HandoverRecord');
