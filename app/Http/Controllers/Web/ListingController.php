<?php

namespace App\Http\Controllers\Web;

use App\Domains\Listing\Http\Controllers\Web\ListingController as DomainListingController;

class_alias(DomainListingController::class, 'App\Http\Controllers\Web\ListingController');
