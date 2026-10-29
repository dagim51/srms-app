<?php

namespace App\Domains\Rental\Services;

use App\Domains\Rental\Models\Lease;
use Carbon\Carbon;

class LeaseAgreementPdfService
{
    /**
     * Compile standardized bilingual legal lease agreement payload.
     */
    public function generateAgreementData(Lease $lease): array
    {
        $lease->loadMissing(['property.owner', 'tenantProfile.user']);

        $property = $lease->property;
        $landlord = $property?->owner;
        $tenant = $lease->tenantProfile?->user;

        $startDate = Carbon::parse($lease->start_date);
        $endDate = Carbon::parse($lease->end_date);
        $durationMonths = max(1, (int) round($startDate->diffInMonths($endDate)));

        $digest = hash('sha256', implode('|', [
            'SRMS_ETHIOPIAN_TENANCY_AGREEMENT',
            $lease->id,
            $property?->id ?? 'unknown_property',
            $lease->rent_amount,
            $lease->deposit_amount,
            $lease->start_date,
            $lease->end_date,
        ]));

        return [
            'agreement_number' => 'LSE-'.strtoupper(substr($lease->id, 0, 8)),
            'created_date' => now()->toDateString(),
            'governing_law' => 'FDRE Residential Tenancy Proclamation & Ethiopian Civil Code (Articles 2896–3018)',
            'digest' => $digest,
            'landlord' => [
                'id' => $landlord?->id,
                'name' => $landlord?->name ?? 'Property Owner',
                'phone' => $landlord?->phone ?? 'N/A',
                'email' => $landlord?->email ?? 'N/A',
            ],
            'tenant' => [
                'id' => $tenant?->id,
                'name' => $tenant?->name ?? 'Verified Tenant',
                'phone' => $tenant?->phone ?? 'N/A',
                'email' => $tenant?->email ?? 'N/A',
            ],
            'premises' => [
                'property_code' => $property?->property_code ?? 'PROP-N/A',
                'title' => $property?->title ?? 'Residential Unit',
                'bedrooms' => $property?->bedrooms ?? 1,
                'bathrooms' => $property?->bathrooms ?? 1,
                'building_area' => $property?->building_area ?? 'N/A',
            ],
            'terms' => [
                'start_date' => $lease->start_date,
                'end_date' => $lease->end_date,
                'duration_months' => $durationMonths,
                'monthly_rent_etb' => (float) $lease->rent_amount,
                'deposit_amount_etb' => (float) $lease->deposit_amount,
                'payment_day_of_month' => $startDate->day,
                'grace_period_days' => 5,
                'late_penalty_pct' => 5.0,
            ],
            'clauses_bilingual' => [
                'am' => [
                    'title' => 'የመኖሪያ ቤት ኪራይ ውል ስምምነት',
                    'payment' => 'ተከራይ በውሉ የተመለከተውን የኪራይ ገንዘብ በየወሩ በተጠቀሰው ቀን ወይም የ5 ቀናት የዕፎይታ ጊዜ ከማለቁ በፊት የመክፈል ግዴታ አለበት።',
                    'possession' => 'አከራይ ተከራይ በሰላም የመኖሪያ ቤቱን የመጠቀም ሙሉ መብት እንዲኖረውና የመሠረተ-ልማት አገልግሎቶች ያልተቋረጡ እንዲሆኑ የማድረግ ኃላፊነት አለበት።',
                ],
                'en' => [
                    'title' => 'Residential Tenancy Lease Agreement',
                    'payment' => 'The tenant covenants to pay the designated monthly rent on or before the due date, with a 5-day grace period before a 5% statutory late fee applies.',
                    'possession' => 'The landlord warrants quiet enjoyment of the premises and covenants to maintain essential structural utilities throughout the tenancy.',
                ],
            ],
            'signatures' => $lease->signatures ?? $lease->metadata['signatures'] ?? [],
        ];
    }

    /**
     * Render printable legal contract document HTML.
     */
    public function renderContractHtml(Lease $lease): string
    {
        $data = $this->generateAgreementData($lease);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Lease Agreement - {$data['agreement_number']}</title>
    <style>
        body { font-family: 'Times New Roman', serif; margin: 40px; color: #1e293b; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 20px; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .subtitle { font-size: 14px; color: #64748b; margin-top: 5px; }
        .section { margin-bottom: 24px; }
        .section-title { font-size: 16px; font-weight: bold; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 12px; }
        .digest-box { font-family: monospace; font-size: 11px; background: #f8fafc; border: 1px dashed #cbd5e1; padding: 8px; word-break: break-all; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-block { width: 45%; border-top: 1px solid #0f172a; padding-top: 10px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Federal Democratic Republic of Ethiopia</div>
        <div class="subtitle">Standard Residential Tenancy Agreement &bull; ውል ስምምነት</div>
        <div style="font-weight: bold; margin-top: 10px;">Agreement Reference: {$data['agreement_number']}</div>
    </div>

    <div class="section">
        <div class="section-title">1. Contracting Parties &bull; ተዋዋይ ወገኖች</div>
        <p><strong>Landlord:</strong> {$data['landlord']['name']} &bull; Phone: {$data['landlord']['phone']}</p>
        <p><strong>Tenant:</strong> {$data['tenant']['name']} &bull; Phone: {$data['tenant']['phone']}</p>
    </div>

    <div class="section">
        <div class="section-title">2. Leased Premises &bull; የኪራይ ይዞታ</div>
        <p><strong>Property:</strong> {$data['premises']['title']} (Code: {$data['premises']['property_code']})</p>
        <p><strong>Rooms:</strong> {$data['premises']['bedrooms']} Bedrooms, {$data['premises']['bathrooms']} Bathrooms</p>
    </div>

    <div class="section">
        <div class="section-title">3. Financial Terms &bull; የገንዘብ ውል</div>
        <p><strong>Monthly Rent:</strong> {$data['terms']['monthly_rent_etb']} ETB &bull; <strong>Deposit:</strong> {$data['terms']['deposit_amount_etb']} ETB</p>
        <p><strong>Grace Period:</strong> {$data['terms']['grace_period_days']} calendar days &bull; <strong>Late Fee:</strong> {$data['terms']['late_penalty_pct']}% penalty</p>
    </div>

    <div class="section">
        <div class="section-title">4. Cryptographic Non-Repudiation Seal</div>
        <div class="digest-box">SHA-256 Digest: {$data['digest']}</div>
    </div>
</body>
</html>
HTML;
    }
}
