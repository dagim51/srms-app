<?php

namespace App\Http\Controllers\Web;

use App\Domains\Organization\Http\Controllers\Web\AgentWebController as DomainAgentWebController;

class_alias(DomainAgentWebController::class, 'App\Http\Controllers\Web\AgentWebController');
