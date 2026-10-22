<?php

namespace App\Http\Controllers\Web;

use App\Domains\Listing\Http\Controllers\Web\CollectionWebController as DomainCollectionWebController;

class_alias(DomainCollectionWebController::class, 'App\Http\Controllers\Web\CollectionWebController');
