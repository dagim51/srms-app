<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IntegrationsWebController extends Controller
{
    public function index()
    {
        $services = [
            ['id' => 'chapa', 'name' => 'Chapa Payments', 'type' => 'Payment Gateway', 'status' => 'connected', 'last_sync' => '2 mins ago', 'icon' => 'card'],
            ['id' => 'telebirr', 'name' => 'Telebirr', 'type' => 'Payment Gateway', 'status' => 'connected', 'last_sync' => '5 mins ago', 'icon' => 'mobile'],
            ['id' => 'cbe', 'name' => 'CBE Birr', 'type' => 'Bank API', 'status' => 'error', 'last_sync' => '4 hours ago', 'icon' => 'bank', 'error' => 'Connection timeout. Retrying...'],
            ['id' => 'google_maps', 'name' => 'Google Maps', 'type' => 'GIS Provider', 'status' => 'connected', 'last_sync' => '1 min ago', 'icon' => 'map'],
            ['id' => 'auth_gov', 'name' => 'National ID API', 'type' => 'Identity Verification', 'status' => 'disconnected', 'last_sync' => 'Never', 'icon' => 'id'],
        ];

        return view('frontend.integrations.index', compact('services'));
    }

    public function history()
    {
        $logs = [
            ['id' => 'LOG-8842', 'service' => 'CBE Birr', 'event' => 'Webhook Delivery Failed', 'timestamp' => '2026-10-08 10:42:11', 'status' => 'failed', 'details' => 'HTTP 504 Gateway Timeout'],
            ['id' => 'LOG-8841', 'service' => 'Chapa Payments', 'event' => 'Payment Confirmed', 'timestamp' => '2026-10-08 10:35:22', 'status' => 'success', 'details' => 'Tx Ref: CHAP-998231'],
            ['id' => 'LOG-8840', 'service' => 'Telebirr', 'event' => 'Payout Processed', 'timestamp' => '2026-10-08 09:15:00', 'status' => 'success', 'details' => 'Batch ID: B-0921'],
            ['id' => 'LOG-8839', 'service' => 'Google Maps', 'event' => 'API Quota Warning', 'timestamp' => '2026-10-07 23:59:59', 'status' => 'warning', 'details' => '85% of monthly quota reached.'],
            ['id' => 'LOG-8838', 'service' => 'National ID API', 'event' => 'OAuth Token Expired', 'timestamp' => '2026-10-05 14:00:00', 'status' => 'failed', 'details' => 'Manual re-authentication required.'],
        ];

        return view('frontend.integrations.history', compact('logs'));
    }
}
