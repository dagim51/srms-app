<?php

namespace App\Models;

use App\Domains\CRM\Models\CrmActivity as DomainCrmActivity;

class_alias(DomainCrmActivity::class, 'App\Models\CrmActivity');
