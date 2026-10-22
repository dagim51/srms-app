<?php

namespace App\Http\Controllers\Web;

use App\Domains\Property\Http\Controllers\Web\FeatureCatalogController as DomainFeatureCatalogController;

class_alias(DomainFeatureCatalogController::class, 'App\Http\Controllers\Web\FeatureCatalogController');
