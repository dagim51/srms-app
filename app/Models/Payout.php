<?php

namespace App\Models;

use App\Domains\Transaction\Models\Payout as DomainPayout;

class_alias(DomainPayout::class, 'App\Models\Payout');
