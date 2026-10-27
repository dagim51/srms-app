<?php

namespace App\Models;

use App\Domains\Transaction\Models\PayoutAccount as DomainPayoutAccount;

class_alias(DomainPayoutAccount::class, 'App\Models\PayoutAccount');
