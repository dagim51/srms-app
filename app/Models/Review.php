<?php

namespace App\Models;

use App\Domains\Review\Models\Review as DomainReview;

class_alias(DomainReview::class, 'App\Models\Review');
