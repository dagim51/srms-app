<?php

namespace App\Http\Controllers\Web;

use App\Domains\Transaction\Http\Controllers\Web\DocumentWebController as DomainDocumentWebController;

class_alias(DomainDocumentWebController::class, 'App\Http\Controllers\Web\DocumentWebController');
