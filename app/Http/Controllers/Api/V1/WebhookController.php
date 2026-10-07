<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, string $provider)
    {
        // 1. Authenticate & Validate Webhook
        $signature = $request->header('X-Signature') ?? '';
        
        // Mocking the verification using our Interface concept. 
        // In a real scenario, we inject the specific gateway based on $provider.
        if (config('services.' . $provider . '.secret') && $signature !== 'valid-signature-for-test') {
            abort(401, 'Invalid signature');
        }

        $payload = $request->all();
        $idempotencyKey = $request->header('Idempotency-Key') ?? ($payload['id'] ?? uniqid());
        $eventType = $payload['type'] ?? 'unknown';

        // 2. Idempotency Check
        $event = WebhookEvent::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'provider' => $provider,
                'event_type' => $eventType,
                'payload' => $payload,
                'status' => 'pending'
            ]
        );

        if (!$event->wasRecentlyCreated && in_array($event->status, ['processed', 'processing'])) {
            return response()->json(['message' => 'Event already processed.'], 200);
        }

        // 3. Process (Isolated from failures corrupting core data)
        try {
            $event->update(['status' => 'processing']);
            
            // Dispatch to a specific isolated job... 
            // e.g. ProcessWebhookJob::dispatch($event)
            // Simulating failure isolation here:
            if ($eventType === 'charge.failed') {
                throw new \Exception('Simulated integration failure');
            }

            $event->update(['status' => 'processed']);
        } catch (\Exception $e) {
            $event->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $event->retry_count + 1
            ]);
            Log::error('Webhook processing failed: ' . $e->getMessage());
            // Return 200 so the provider doesn't overwhelm with retries, 
            // or 500 depending on provider docs. We'll return 200 and retry via our own CRON.
        }

        return response()->json(['message' => 'Received'], 200);
    }
}
