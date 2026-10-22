<?php

namespace App\Http\Controllers\Web;

use App\Domains\Review\Http\Controllers\Web\ReviewWebController as DomainReviewWebController;

class_alias(DomainReviewWebController::class, 'App\Http\Controllers\Web\ReviewWebController');
