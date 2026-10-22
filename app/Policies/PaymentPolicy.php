<?php

namespace App\Policies;

use App\Domains\Transaction\Policies\PaymentPolicy as DomainPaymentPolicy;

class_alias(DomainPaymentPolicy::class, 'App\Policies\PaymentPolicy');
