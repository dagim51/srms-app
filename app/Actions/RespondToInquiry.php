<?php

namespace App\Actions;

use App\Models\Inquiry;

class RespondToInquiry extends Action
{
    /**
     * Execute the respond action.
     */
    public function execute(Inquiry $inquiry, string $response, string $status = 'responded'): Inquiry
    {
        return $this->transactional(function () use ($inquiry, $response, $status) {
            $inquiry->status = $status;
            $inquiry->response = $response;
            $inquiry->responded_at = now();
            $inquiry->save();

            return $inquiry->fresh();
        });
    }
}
