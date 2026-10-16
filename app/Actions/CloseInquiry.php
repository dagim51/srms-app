<?php

namespace App\Actions;

use App\Models\Inquiry;

class CloseInquiry extends Action
{
    /**
     * Execute the close action.
     */
    public function execute(Inquiry $inquiry): Inquiry
    {
        return $this->transactional(function () use ($inquiry) {
            $inquiry->status = 'closed';
            $inquiry->closed_at = now();
            $inquiry->save();

            return $inquiry->fresh();
        });
    }
}