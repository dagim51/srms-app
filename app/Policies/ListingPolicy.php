<?php

namespace App\Policies;

use App\Domains\Listing\Policies\ListingPolicy as DomainListingPolicy;

class_alias(DomainListingPolicy::class, 'App\Policies\ListingPolicy');
