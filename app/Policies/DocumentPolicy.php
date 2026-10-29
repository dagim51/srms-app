<?php

namespace App\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Transaction\Models\Document;
use App\Models\AccommodationBooking;
use App\Models\Lease;
use App\Models\Offer;
use App\Models\Property;

class DocumentPolicy
{
    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // 1. Super-admin override
        if ($user->hasRole('Administrator') || $user->hasPermissionTo('admin')) {
            return true;
        }

        // 2. Owner always has access to their own document
        if ($user->id === $document->user_id) {
            return true;
        }

        // 3. Document Access Level checks (SRS Section 28 & 144)
        $level = strtolower($document->access_level ?? 'private');

        return match ($level) {
            'public' => true,
            'verification-only', 'verification_only' => $user->hasAnyPermission(['kyc.verify', 'documents.verify'])
                || $user->hasAnyRole(['Verification Officer', 'Moderator']),
            'admin-only', 'admin_only' => false, // Handled above by Administrator check
            'transaction_participants', 'transaction-participants' => $this->isTransactionParticipant($user, $document),
            'private' => false,
            default => false,
        };
    }

    /**
     * Determine whether the user can download the document.
     */
    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    /**
     * Determine whether the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        return $user->id === $document->user_id;
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        return $this->update($user, $document);
    }

    /**
     * Dynamically inspect related transactions to determine if the user is an authorized
     * transaction participant (e.g., buyer, seller, host, tenant, or assigned escrow officer).
     */
    protected function isTransactionParticipant(User $user, Document $document): bool
    {
        // 1. Owner/Uploader
        if ($user->id === $document->user_id) {
            return true;
        }

        // 2. Assigned Escrow / Finance Officers overseeing platform deal disbursements
        if ($user->hasAnyRole(['Finance Officer']) || $user->hasPermissionTo('finance.escrow.release')) {
            return true;
        }

        // 3. Documentable Polymorphic Target Evaluation
        if ($document->documentable_type && $document->documentable_id) {
            $target = $document->documentable;

            if ($target instanceof Property) {
                if ($this->isPropertyTransactionParticipant($user, $target)) {
                    return true;
                }
            } elseif ($target instanceof Offer) {
                if ($target->buyer_id === $user->id) {
                    return true;
                }
                $sellerId = $target->listing?->property?->owner_user_id;
                if ($sellerId && $sellerId === $user->id) {
                    return true;
                }
            } elseif ($target instanceof Lease) {
                if ($target->tenantProfile?->user_id === $user->id) {
                    return true;
                }
                if ($target->property?->owner_user_id === $user->id) {
                    return true;
                }
            } elseif ($target instanceof AccommodationBooking) {
                if ($target->user_id === $user->id) {
                    return true;
                }
                if ($target->accommodation?->property?->owner_user_id === $user->id) {
                    return true;
                }
            }
        }

        // 4. Reverse Deed Reference check (e.g., Property deed document)
        $deedProperty = Property::where('deed_document_id', $document->id)->first();
        if ($deedProperty && $this->isPropertyTransactionParticipant($user, $deedProperty)) {
            return true;
        }

        // 5. Active Transaction Counterparty Check between $user and $document->user_id
        if ($document->user_id) {
            $docOwnerId = $document->user_id;

            // Direct active offer between them
            $hasActiveOffer = Offer::where(function ($q) use ($user, $docOwnerId) {
                $q->where('buyer_id', $user->id)
                    ->whereHas('listing.property', fn ($p) => $p->where('owner_user_id', $docOwnerId));
            })->orWhere(function ($q) use ($user, $docOwnerId) {
                $q->where('buyer_id', $docOwnerId)
                    ->whereHas('listing.property', fn ($p) => $p->where('owner_user_id', $user->id));
            })->exists();

            if ($hasActiveOffer) {
                return true;
            }

            // Direct active lease between them
            $hasActiveLease = Lease::where(function ($q) use ($user, $docOwnerId) {
                $q->whereHas('tenantProfile', fn ($tp) => $tp->where('user_id', $user->id))
                    ->whereHas('property', fn ($p) => $p->where('owner_user_id', $docOwnerId));
            })->orWhere(function ($q) use ($user, $docOwnerId) {
                $q->whereHas('tenantProfile', fn ($tp) => $tp->where('user_id', $docOwnerId))
                    ->whereHas('property', fn ($p) => $p->where('owner_user_id', $user->id));
            })->exists();

            if ($hasActiveLease) {
                return true;
            }

            // Direct booking between them
            $hasActiveBooking = AccommodationBooking::where(function ($q) use ($user, $docOwnerId) {
                $q->where('user_id', $user->id)
                    ->whereHas('accommodation.property', fn ($p) => $p->where('owner_user_id', $docOwnerId));
            })->orWhere(function ($q) use ($user, $docOwnerId) {
                $q->where('user_id', $docOwnerId)
                    ->whereHas('accommodation.property', fn ($p) => $p->where('owner_user_id', $user->id));
            })->exists();

            if ($hasActiveBooking) {
                return true;
            }
        }

        return false;
    }

    protected function isPropertyTransactionParticipant(User $user, Property $property): bool
    {
        // Owner or Assigned Agent
        if ($property->owner_user_id === $user->id || $property->agent_user_id === $user->id) {
            return true;
        }

        // Active Offer Buyer
        $hasOffer = Offer::whereHas('listing', fn ($q) => $q->where('property_id', $property->id))
            ->where('buyer_id', $user->id)
            ->whereIn('status', ['pending', 'countered', 'accepted'])
            ->exists();

        if ($hasOffer) {
            return true;
        }

        // Active Tenant on Lease
        $hasLease = Lease::where('property_id', $property->id)
            ->whereHas('tenantProfile', fn ($q) => $q->where('user_id', $user->id))
            ->exists();

        if ($hasLease) {
            return true;
        }

        // Active Guest on Booking
        $hasBooking = AccommodationBooking::whereHas('accommodation', fn ($q) => $q->where('property_id', $property->id))
            ->where('user_id', $user->id)
            ->exists();

        return $hasBooking;
    }
}
