<?php
namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function verifyWebhookSignature(string $payload, string $signature): bool;
    public function processEvent(array $payload): void;
}
