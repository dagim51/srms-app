<?php

namespace App\Models;

use App\Domains\Identity\Models\LoginEvent as DomainLoginEvent;

class_alias(DomainLoginEvent::class, 'App\Models\LoginEvent');
