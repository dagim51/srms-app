<?php

namespace App\Http\Controllers\Web;

use App\Domains\Development\Http\Controllers\Web\DeveloperProjectWebController as DomainDeveloperProjectWebController;

class_alias(DomainDeveloperProjectWebController::class, 'App\Http\Controllers\Web\DeveloperProjectWebController');
