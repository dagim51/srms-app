<?php

namespace App\Http\Controllers\Web;

use App\Domains\Transaction\Http\Controllers\Web\TransactionWebController as DomainTransactionWebController;

class_alias(DomainTransactionWebController::class, 'App\Http\Controllers\Web\TransactionWebController');
