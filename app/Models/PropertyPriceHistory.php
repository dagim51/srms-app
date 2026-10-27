<?php

namespace App\Models;

use App\Domains\Property\Models\PropertyPriceHistory as DomainPropertyPriceHistory;

class_alias(DomainPropertyPriceHistory::class, 'App\Models\PropertyPriceHistory');
