<?php

namespace App\Http\Controllers\Web;

use App\Domains\Engagement\Http\Controllers\Web\MessageWebController as DomainMessageWebController;

class_alias(DomainMessageWebController::class, 'App\Http\Controllers\Web\MessageWebController');
