<?php

namespace App\Http\Controllers\Web;

use App\Domains\Organization\Http\Controllers\Web\OrganizationWebController as DomainOrganizationWebController;

class_alias(DomainOrganizationWebController::class, 'App\Http\Controllers\Web\OrganizationWebController');
