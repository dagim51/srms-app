<?php

namespace App\Http\Controllers\Web;

use App\Domains\Property\Http\Controllers\Web\PublicPropertyWebController as DomainPublicPropertyWebController;

class_alias(DomainPublicPropertyWebController::class, 'App\Http\Controllers\Web\PublicPropertyWebController');
