<?php

namespace App\Http\Controllers\Web;

use App\Domains\Administration\Http\Controllers\Web\AdminWebController as DomainAdminWebController;

class_alias(DomainAdminWebController::class, 'App\Http\Controllers\Web\AdminWebController');
