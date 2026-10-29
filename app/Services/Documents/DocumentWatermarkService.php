<?php

namespace App\Services\Documents;

use App\Domains\Identity\Models\User;
use App\Domains\Property\Models\Property;
use App\Domains\Transaction\Models\Document;
use App\Domains\Transaction\Models\DocumentAccessLog;
use Carbon\Carbon;

class DocumentWatermarkService
{
    /**
     * Generate structured cryptographic and forensic metadata for dynamic watermarking.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function generateWatermarkMetadata(User $viewer, string $ipAddress, ?Document $document = null, array $context = []): array
    {
        $now = Carbon::now();
        $timestamp = $now->toIso8601String();
        $humanDate = $now->format('Y-m-d H:i:s T');

        $payload = implode('|', [
            'SRMS_ETHIOPIA_CADASTRAL_VAULT',
            $viewer->id,
            $viewer->name,
            $ipAddress,
            $timestamp,
            $document ? $document->id : 'canonical_carta',
            config('app.key', 'srms_secure_key'),
        ]);

        $securityToken = hash('sha256', $payload);
        $shortToken = strtoupper(substr($securityToken, 0, 16));

        $legalNotice = "OFFICIAL RECORD &bull; CONFIDENTIAL &bull; ISSUED TO: {$viewer->name} (UID: {$viewer->id}) &bull; IP: {$ipAddress} &bull; {$humanDate} &bull; REF: #{$shortToken}";

        return [
            'viewer_id' => $viewer->id,
            'viewer_name' => $viewer->name,
            'viewer_email' => $viewer->email,
            'ip_address' => $ipAddress,
            'timestamp' => $timestamp,
            'human_date' => $humanDate,
            'security_token' => $securityToken,
            'short_token' => $shortToken,
            'legal_notice' => $legalNotice,
            'context' => $context,
        ];
    }

    /**
     * Apply dynamic forensic watermark to content and log the access in audit records.
     *
     * @param  array<string, mixed>  $context
     */
    public function watermarkContent(string $rawContent, User $viewer, string $ipAddress, ?Document $document = null, array $context = []): string
    {
        $meta = $this->generateWatermarkMetadata($viewer, $ipAddress, $document, $context);

        // Record audit access log
        if ($document) {
            DocumentAccessLog::create([
                'document_id' => $document->id,
                'user_id' => $viewer->id,
                'ip_address' => $ipAddress,
                'action' => 'download_watermarked',
            ]);
        }

        // If content is an HTML document or canonical string
        if (str_contains($rawContent, '</body>') || str_contains($rawContent, '</html>')) {
            $watermarkBanner = <<<HTML
<div id="srms-forensic-watermark" style="position: fixed; bottom: 0; left: 0; right: 0; background: rgba(15, 23, 42, 0.92); color: #f8fafc; font-size: 11px; padding: 6px 16px; text-align: center; font-family: monospace; z-index: 99999; border-top: 2px solid #ef4444;">
    {$meta['legal_notice']}
</div>
<div id="srms-diagonal-watermark" style="position: fixed; top: 35%; left: 10%; right: 10%; text-align: center; transform: rotate(-30deg); font-size: 42px; font-weight: bold; color: rgba(220, 38, 38, 0.08); pointer-events: none; z-index: 9999; font-family: sans-serif; text-transform: uppercase;">
    VERIFIED &bull; {$viewer->name} &bull; {$meta['short_token']}
</div>
HTML;

            return str_replace('</body>', $watermarkBanner.'</body>', $rawContent);
        }

        // If raw PDF binary or mock PDF (starts with %PDF)
        if (str_starts_with($rawContent, '%PDF')) {
            $forensicTrailer = "\n%SRMS_FORENSIC_WATERMARK_START%\n"
                .'% VIEWER: '.$viewer->name." (ID: {$viewer->id})\n"
                .'% IP: '.$ipAddress."\n"
                .'% TIMESTAMP: '.$meta['timestamp']."\n"
                .'% TOKEN: '.$meta['security_token']."\n"
                .'% NOTICE: '.$meta['legal_notice']."\n"
                ."%SRMS_FORENSIC_WATERMARK_END%\n";

            return $rawContent.$forensicTrailer;
        }

        // Generic text/content
        return $rawContent."\n\n[FORENSIC WATERMARK: {$meta['legal_notice']}]";
    }

    /**
     * Generate an official canonical Ethiopian Title Deed (Carta) document.
     */
    public function generateCanonicalCartaDeed(Property $property): string
    {
        $property->loadMissing(['owner', 'subCity', 'woreda', 'region']);

        $cartaNo = $property->carta_number ?? 'CRTA-'.strtoupper(substr($property->id, 0, 8));
        $parcelId = $property->cadastral_parcel_id ?? 'UNREGISTERED-PARCEL-001';
        $blockNo = $property->block_number ?? 'BLK-01';
        $woredaSheet = $property->woreda_cadastre_sheet ?? 'WS-001';
        $tenureType = ucwords(str_replace('_', ' ', (string) ($property->land_tenure_type ?? 'leasehold_99yr')));
        $yearsRemaining = $property->lease_years_remaining ?? 99;
        $owner = $property->owner;
        $ownerName = ($owner instanceof User) ? $owner->name : 'Verified Property Owner';
        $location = $property->location_summary;

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Ethiopian Title Deed (Carta) - {$cartaNo}</title>
    <style>
        body { font-family: 'Times New Roman', serif; margin: 40px; color: #0f172a; line-height: 1.6; }
        .crest { text-align: center; border-bottom: 3px double #0f172a; padding-bottom: 20px; margin-bottom: 25px; }
        .gov-title { font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .agency-title { font-size: 15px; font-weight: 600; color: #334155; margin-top: 4px; }
        .deed-title { font-size: 22px; font-weight: bold; color: #1e3a8a; margin-top: 15px; text-decoration: underline; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 20px 0; }
        .field { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 4px; }
        .field-label { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 2px; }
        .field-value { font-size: 15px; font-weight: 600; color: #0f172a; }
        .tenure-badge { display: inline-block; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .seal-box { margin-top: 30px; border-top: 2px solid #cbd5e1; padding-top: 15px; display: flex; justify-content: space-between; align-items: flex-end; }
        .qr-placeholder { border: 2px dashed #94a3b8; width: 90px; height: 90px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="crest">
        <div class="gov-title">Federal Democratic Republic of Ethiopia</div>
        <div class="agency-title">Addis Ababa City Administration &bull; Land Holding & Cadastral Registration Agency</div>
        <div class="agency-title">የመሬት ይዞታ ምዝገባ እና መረጃ ኤጀንሲ (ካርታ)</div>
        <div class="deed-title">Official Real Property Title Deed (ካርታ)</div>
        <div style="margin-top: 6px; font-weight: bold; color: #dc2626;">Certificate Reference: {$cartaNo}</div>
    </div>

    <div class="grid">
        <div class="field">
            <div class="field-label">Cadastral Parcel Identifier (የይዞታ መለያ)</div>
            <div class="field-value">{$parcelId}</div>
        </div>
        <div class="field">
            <div class="field-label">Cadastre Block & Sheet Number</div>
            <div class="field-value">Block {$blockNo} &bull; Sheet {$woredaSheet}</div>
        </div>
        <div class="field">
            <div class="field-label">Registered Legal Landholder (ባለይዞታ)</div>
            <div class="field-value">{$ownerName}</div>
        </div>
        <div class="field">
            <div class="field-label">Land Tenure System (የይዞታው አይነት)</div>
            <div class="field-value"><span class="tenure-badge">{$tenureType} ({$yearsRemaining} Yrs Remaining)</span></div>
        </div>
        <div class="field">
            <div class="field-label">Property Description & Code</div>
            <div class="field-value">{$property->title} ({$property->property_code})</div>
        </div>
        <div class="field">
            <div class="field-label">Administrative Jurisdiction</div>
            <div class="field-value">{$location}</div>
        </div>
    </div>

    <div class="seal-box">
        <div>
            <p style="margin: 0; font-size: 12px; color: #64748b;">Issued under FDRE Urban Landholding Proclamation No. 721/2011.</p>
            <p style="margin: 4px 0 0 0; font-size: 12px; font-weight: bold;">Registrar Seal & Signature: ________________________________</p>
        </div>
        <div class="qr-placeholder">
            [CADASTRAL<br>SEAL]
        </div>
    </div>
</body>
</html>
HTML;
    }
}
