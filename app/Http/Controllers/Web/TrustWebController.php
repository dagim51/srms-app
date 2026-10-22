<?php

namespace App\Http\Controllers\Web;

use App\Domains\Trust\Http\Controllers\Web\TrustWebController as DomainTrustWebController;

class_alias(DomainTrustWebController::class, 'App\Http\Controllers\Web\TrustWebController');
