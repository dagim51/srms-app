<?php

namespace App\Http\Controllers\Web;

use App\Domains\CRM\Http\Controllers\Web\CrmWebController as DomainCrmWebController;

class_alias(DomainCrmWebController::class, 'App\Http\Controllers\Web\CrmWebController');
