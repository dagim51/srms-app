<?php

namespace App\Http\Controllers\Web;

use App\Domains\Location\Http\Controllers\Web\LocationWebController as DomainLocationWebController;

class_alias(DomainLocationWebController::class, 'App\Http\Controllers\Web\LocationWebController');
