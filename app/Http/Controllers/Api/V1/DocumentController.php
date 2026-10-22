<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Transaction\Http\Controllers\Api\V1\DocumentController as DomainDocumentController;

class_alias(DomainDocumentController::class, 'App\Http\Controllers\Api\V1\DocumentController');
