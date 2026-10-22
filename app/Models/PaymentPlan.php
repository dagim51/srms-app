<?php

namespace App\Models;

use App\Domains\Development\Models\PaymentPlan as DomainPaymentPlan;

class_alias(DomainPaymentPlan::class, 'App\Models\PaymentPlan');
