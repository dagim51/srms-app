<?php

namespace App\Models;

use App\Domains\Development\Models\PaymentPlanInstallment as DomainPaymentPlanInstallment;

class_alias(DomainPaymentPlanInstallment::class, 'App\Models\PaymentPlanInstallment');
