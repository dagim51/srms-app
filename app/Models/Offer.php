<?php

namespace App\Models;

use App\Domains\Engagement\Models\Offer as DomainOffer;

class_alias(DomainOffer::class, 'App\Models\Offer');
