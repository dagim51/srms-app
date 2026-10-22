<?php

namespace App\Http\Controllers\Web;

use App\Domains\Property\Http\Controllers\Web\PropertyWebController as DomainPropertyWebController;

class_alias(DomainPropertyWebController::class, 'App\Http\Controllers\Web\PropertyWebController');
