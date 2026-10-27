<?php

namespace App\Services\Trust;

use App\Domains\Trust\Services\FraudDetectionService as DomainFraudDetectionService;

class_alias(DomainFraudDetectionService::class, 'App\Services\Trust\FraudDetectionService');
class_alias(DomainFraudDetectionService::class, 'App\Services\FraudDetectionService');
