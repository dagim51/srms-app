<?php

namespace App\Http\Controllers\Web;

use App\Domains\Rental\Http\Controllers\Web\RentalWebController as DomainRentalWebController;

class_alias(DomainRentalWebController::class, 'App\Http\Controllers\Web\RentalWebController');
