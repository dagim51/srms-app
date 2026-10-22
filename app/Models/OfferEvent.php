<?php

namespace App\Models;

use App\Domains\Engagement\Models\OfferEvent as DomainOfferEvent;

class_alias(DomainOfferEvent::class, 'App\Models\OfferEvent');
