<?php

namespace App\Models;

use App\Domains\PropertyManagement\Models\CleaningTask as DomainCleaningTask;

class_alias(DomainCleaningTask::class, 'App\Models\CleaningTask');
