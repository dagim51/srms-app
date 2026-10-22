<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Api\V1\InquiryController as DomainInquiryController;

class_alias(DomainInquiryController::class, 'App\Http\Controllers\Api\V1\InquiryController');
