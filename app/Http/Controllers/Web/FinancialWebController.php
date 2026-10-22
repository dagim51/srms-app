<?php

namespace App\Http\Controllers\Web;

use App\Domains\Transaction\Http\Controllers\Web\FinancialWebController as DomainFinancialWebController;

class_alias(DomainFinancialWebController::class, 'App\Http\Controllers\Web\FinancialWebController');
