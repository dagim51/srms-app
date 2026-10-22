<?php

namespace App\Models;

use App\Domains\PropertyManagement\Models\MaintenanceTicket as DomainMaintenanceTicket;

class_alias(DomainMaintenanceTicket::class, 'App\Models\MaintenanceTicket');
