<?php

namespace App\Http\Controllers\Web;

use App\Domains\GIS\Http\Controllers\Web\GisWebController as DomainGisWebController;

class_alias(DomainGisWebController::class, 'App\Http\Controllers\Web\GisWebController');
