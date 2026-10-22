<?php

namespace App\Services\Inquiries;

use App\Actions\CloseInquiry;
use App\Actions\CreateInquiry;
use App\Actions\RespondToInquiry;
use App\Events\Inquiries\InquiryCreated;
use App\Events\Inquiries\InquiryResponded;
use App\Models\Inquiry;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InquiryService
{
    /**
     * Create a new inquiry for a listing.
     */
    public function create(array $data, string $requesterId): Inquiry
    {
        return DB::transaction(function () use ($data, $requesterId) {
            $listing = Listing::findOrFail($data['listing_id']);

            $action = new CreateInquiry;
            $inquiry = $action->execute($listing, $requesterId, $data['message'] ?? null);

            event(new InquiryCreated($inquiry));

            return $inquiry->fresh();
        });
    }

    /**
     * Respond to an inquiry (seller/agent action).
     */
    public function respond(Inquiry $inquiry, string $response, ?string $status = 'responded'): Inquiry
    {
        return DB::transaction(function () use ($inquiry, $response, $status) {
            if (! in_array($inquiry->status, ['pending', 'responded'])) {
                throw new \InvalidArgumentException('Only pending or responded inquiries can be updated');
            }

            $action = new RespondToInquiry;
            $respondedInquiry = $action->execute($inquiry, $response, $status);

            event(new InquiryResponded($respondedInquiry, $response));

            return $respondedInquiry;
        });
    }

    /**
     * Close an inquiry.
     */
    public function close(Inquiry $inquiry): Inquiry
    {
        return DB::transaction(function () use ($inquiry) {
            if ($inquiry->status === 'closed') {
                throw new \InvalidArgumentException('Inquiry is already closed');
            }

            $action = new CloseInquiry;
            $closedInquiry = $action->execute($inquiry);

            // No event for close as it's a terminal state

            return $closedInquiry;
        });
    }

    /**
     * Bulk respond to inquiries.
     */
    public function bulkRespond(Collection $inquiries, string $response, ?string $status = 'responded'): Collection
    {
        return $inquiries->map(function ($inquiry) use ($response, $status) {
            return $this->respond($inquiry, $response, $status);
        });
    }

    /**
     * Bulk close inquiries.
     */
    public function bulkClose(Collection $inquiries): Collection
    {
        return $inquiries->map(function ($inquiry) {
            return $this->close($inquiry);
        });
    }
}
