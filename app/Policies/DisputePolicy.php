<?php

namespace App\Policies;

use App\Domains\Trust\Policies\DisputePolicy as DomainDisputePolicy;

class_alias(DomainDisputePolicy::class, 'App\Policies\DisputePolicy');
