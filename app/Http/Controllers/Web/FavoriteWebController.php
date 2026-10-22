<?php

namespace App\Http\Controllers\Web;

use App\Domains\Listing\Http\Controllers\Web\FavoriteWebController as DomainFavoriteWebController;

class_alias(DomainFavoriteWebController::class, 'App\Http\Controllers\Web\FavoriteWebController');
