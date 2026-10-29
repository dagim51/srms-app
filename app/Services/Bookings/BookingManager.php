<?php

namespace App\Services\Bookings;

use App\Actions\CancelBooking;
use App\Actions\ConfirmBooking;
use App\Events\Bookings\BookingCancelled;
use App\Events\Bookings\BookingConfirmed;
use App\Events\Bookings\BookingCreated;
use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use App\Models\AccommodationBooking;
use App\Models\AccommodationPricingRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingManager
{
    /**
     * Create a new booking request.
     */
    public function create(array $data, string $userId): AccommodationBooking
    {
        return DB::transaction(function () use ($data, $userId) {
            $accommodation = Accommodation::where('id', $data['accommodation_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // Prevent host from booking their own accommodation
            if ($accommodation->host_id === $userId) {
                throw new \InvalidArgumentException('You cannot book your own accommodation.');
            }

            if ($data['guests'] > $accommodation->max_guests) {
                throw new \InvalidArgumentException('Number of guests exceeds maximum allowed.');
            }

            // Validate accommodation availability
            $this->validateAvailability($accommodation, $data['check_in_date'], $data['check_out_date']);

            // Calculate price
            $totalPrice = $this->calculatePrice($accommodation, $data['check_in_date'], $data['check_out_date']);

            // Create booking
            $booking = AccommodationBooking::create([
                'accommodation_id' => $accommodation->id,
                'user_id' => $userId,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'guests' => $data['guests'],
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // Record the booking as an availability block
            AccommodationAvailability::create([
                'accommodation_id' => $accommodation->id,
                'start_date' => $data['check_in_date'],
                'end_date' => $data['check_out_date'],
                'type' => 'booked',
                'reason' => 'Booking ID: '.$booking->id,
            ]);

            // Fire domain event
            event(new BookingCreated($booking));

            return $booking->fresh();
        });
    }

    /**
     * Create a temporary reservation hold (default 15 minutes).
     */
    public function createHold(array $data, string $userId, int $holdMinutes = 15): AccommodationBooking
    {
        return DB::transaction(function () use ($data, $userId, $holdMinutes) {
            $accommodation = Accommodation::where('id', $data['accommodation_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // Prevent host from booking their own accommodation
            if ($accommodation->host_id === $userId) {
                throw new \InvalidArgumentException('You cannot book your own accommodation.');
            }

            if ($data['guests'] > $accommodation->max_guests) {
                throw new \InvalidArgumentException('Number of guests exceeds maximum allowed.');
            }

            // Validate accommodation availability
            $this->validateAvailability($accommodation, $data['check_in_date'], $data['check_out_date']);

            // Calculate price
            $totalPrice = $this->calculatePrice($accommodation, $data['check_in_date'], $data['check_out_date']);

            $expiresAt = Carbon::now()->addMinutes($holdMinutes);

            // Create temporary booking hold
            $booking = AccommodationBooking::create([
                'accommodation_id' => $accommodation->id,
                'user_id' => $userId,
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'guests' => $data['guests'],
                'total_price' => $totalPrice,
                'status' => 'holding',
                'expires_at' => $expiresAt,
            ]);

            // Record the hold as an availability block
            AccommodationAvailability::create([
                'accommodation_id' => $accommodation->id,
                'start_date' => $data['check_in_date'],
                'end_date' => $data['check_out_date'],
                'type' => 'held',
                'reason' => 'Hold ID: '.$booking->id,
            ]);

            event(new BookingCreated($booking));

            return $booking->fresh();
        });
    }

    /**
     * Confirm a pending or holding booking.
     */
    public function confirm(AccommodationBooking $booking, ?string $confirmedBy = null): AccommodationBooking
    {
        return DB::transaction(function () use ($booking, $confirmedBy) {
            if (! in_array($booking->status, ['pending', 'holding'])) {
                throw new \InvalidArgumentException('Only pending or holding bookings can be confirmed');
            }

            if ($booking->status === 'holding' && $booking->expires_at && $booking->expires_at->isPast()) {
                throw new \DomainException('The reservation hold has expired');
            }

            // Upgrade availability block from held to booked if applicable
            AccommodationAvailability::where('accommodation_id', $booking->accommodation_id)
                ->where('reason', 'Hold ID: '.$booking->id)
                ->update([
                    'type' => 'booked',
                    'reason' => 'Booking ID: '.$booking->id,
                ]);

            $action = new ConfirmBooking;
            $confirmedBooking = $action->execute($booking);

            event(new BookingConfirmed($confirmedBooking, $confirmedBy));

            return $confirmedBooking;
        });
    }

    /**
     * Cancel a booking (guest or host action).
     */
    public function cancel(AccommodationBooking $booking, ?string $cancelledBy = null, string $reason = 'guest_cancelled'): AccommodationBooking
    {
        return DB::transaction(function () use ($booking, $cancelledBy, $reason) {
            if (! in_array($booking->status, ['pending', 'confirmed', 'holding'])) {
                throw new \InvalidArgumentException('Only pending, confirmed, or holding bookings can be cancelled');
            }

            // Remove availability blocks associated with this booking
            AccommodationAvailability::where('accommodation_id', $booking->accommodation_id)
                ->where(function ($q) use ($booking) {
                    $q->where('reason', 'Booking ID: '.$booking->id)
                        ->orWhere('reason', 'Hold ID: '.$booking->id);
                })
                ->delete();

            $action = new CancelBooking;
            $cancelledBooking = $action->execute($booking);

            event(new BookingCancelled($cancelledBooking, $cancelledBy, $reason));

            return $cancelledBooking;
        });
    }

    /**
     * Validate accommodation availability for given dates.
     */
    protected function validateAvailability(Accommodation $accommodation, string $checkIn, string $checkOut): void
    {
        // Check availability blocks, excluding any expired holds
        $conflict = AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where('start_date', '<', $checkOut)
            ->where('end_date', '>', $checkIn)
            ->where(function ($q) {
                $q->where('type', '!=', 'held')
                    ->orWhereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('accommodation_bookings')
                            ->whereRaw("accommodation_availabilities.reason = 'Hold ID: ' || accommodation_bookings.id")
                            ->where('status', 'holding')
                            ->where(function ($exp) {
                                $exp->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', now());
                            });
                    });
            })
            ->exists();

        if ($conflict) {
            throw new \DomainException('The selected dates are not available');
        }

        // Check existing confirmed/pending bookings and unexpired active holds
        $bookingConflict = AccommodationBooking::where('accommodation_id', $accommodation->id)
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'confirmed'])
                    ->orWhere(function ($q) {
                        $q->where('status', 'holding')
                            ->where(function ($sub) {
                                $sub->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', now());
                            });
                    });
            })
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->exists();

        if ($bookingConflict) {
            throw new \DomainException('The selected dates are already booked');
        }
    }

    /**
     * Calculate total price based on base price and dynamic pricing rules.
     */
    public function calculatePrice(Accommodation $accommodation, string $checkIn, string $checkOut): float
    {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);
        $basePrice = (float) ($accommodation->listing->price ?? $accommodation->property->price ?? 100);

        $candidateRules = AccommodationPricingRule::where('accommodation_id', $accommodation->id)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereNull('start_date')
                    ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                        $q2->where('start_date', '<', $checkOut)
                            ->where('end_date', '>', $checkIn);
                    });
            })
            ->orderByDesc('priority')
            ->get();

        $totalPrice = 0.0;
        $current = $start->copy();

        while ($current->lt($end)) {
            $dateStr = $current->toDateString();
            $isWeekend = in_array($current->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY]);
            $dayRate = $basePrice;

            $matchingRule = $candidateRules->first(function ($rule) use ($dateStr, $isWeekend) {
                if ($rule->start_date && $rule->end_date) {
                    $ruleStart = Carbon::parse($rule->start_date)->toDateString();
                    $ruleEnd = Carbon::parse($rule->end_date)->toDateString();
                    if ($dateStr < $ruleStart || $dateStr >= $ruleEnd) {
                        return false;
                    }
                }

                if ($rule->type === 'weekend' && ! $isWeekend) {
                    return false;
                }

                return true;
            });

            if ($matchingRule) {
                if ($matchingRule->price_modifier_type === 'fixed') {
                    $dayRate = (float) $matchingRule->price_modifier_value;
                } else {
                    $dayRate = $dayRate * (1 + ((float) $matchingRule->price_modifier_value / 100));
                }
            }

            $totalPrice += $dayRate;
            $current->addDay();
        }

        return round($totalPrice, 2);
    }

    /**
     * Calculate an itemized detailed price breakdown including dynamic rules,
     * weekend surcharges, cleaning fee, platform service fee, and taxes.
     */
    public function calculateDetailedPrice(Accommodation $accommodation, string $checkIn, string $checkOut, array $options = []): array
    {
        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);
        $nights = max(1, (int) $start->diffInDays($end));

        $baseNightlyRate = (float) ($accommodation->listing->price ?? $accommodation->property->price ?? 100);
        $baseSubtotal = $baseNightlyRate * $nights;

        $candidateRules = AccommodationPricingRule::where('accommodation_id', $accommodation->id)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereNull('start_date')
                    ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                        $q2->where('start_date', '<', $checkOut)
                            ->where('end_date', '>', $checkIn);
                    });
            })
            ->orderByDesc('priority')
            ->get();

        $dailyBreakdown = [];
        $weekendNights = 0;
        $staySubtotal = 0.0;
        $pricingAdjustments = [];
        $current = $start->copy();

        while ($current->lt($end)) {
            $dateStr = $current->toDateString();
            $isWeekend = in_array($current->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY]);
            if ($isWeekend) {
                $weekendNights++;
            }

            $effectiveRate = $baseNightlyRate;
            $appliedRule = $candidateRules->first(function ($rule) use ($dateStr, $isWeekend) {
                if ($rule->start_date && $rule->end_date) {
                    $ruleStart = Carbon::parse($rule->start_date)->toDateString();
                    $ruleEnd = Carbon::parse($rule->end_date)->toDateString();
                    if ($dateStr < $ruleStart || $dateStr >= $ruleEnd) {
                        return false;
                    }
                }

                if ($rule->type === 'weekend' && ! $isWeekend) {
                    return false;
                }

                return true;
            });

            if ($appliedRule) {
                if ($appliedRule->price_modifier_type === 'fixed') {
                    $effectiveRate = (float) $appliedRule->price_modifier_value;
                } else {
                    $effectiveRate = $effectiveRate * (1 + ((float) $appliedRule->price_modifier_value / 100));
                }

                $pricingAdjustments[$appliedRule->id] = [
                    'rule_id' => $appliedRule->id,
                    'type' => $appliedRule->type,
                    'modifier_type' => $appliedRule->price_modifier_type,
                    'modifier_value' => (float) $appliedRule->price_modifier_value,
                ];
            }

            $dailyBreakdown[] = [
                'date' => $dateStr,
                'day_of_week' => $current->format('l'),
                'is_weekend' => $isWeekend,
                'rate' => round($effectiveRate, 2),
                'rule_applied' => $appliedRule ? $appliedRule->type : null,
            ];

            $staySubtotal += $effectiveRate;
            $current->addDay();
        }

        $weekendMultiplier = (float) ($options['weekend_multiplier'] ?? 0.10);
        $weekendSurcharge = round($weekendNights * $baseNightlyRate * $weekendMultiplier, 2);
        $staySubtotal = round($staySubtotal + $weekendSurcharge, 2);

        // Mandatory fees
        $cleaningFee = (float) ($options['cleaning_fee'] ?? 50.00);
        $serviceFeeRate = $options['service_fee_rate'] ?? 0.05; // 5% service fee
        $serviceFee = round($staySubtotal * $serviceFeeRate, 2);
        $taxRate = $options['tax_rate'] ?? 0.15; // 15% VAT
        $taxAmount = round(($staySubtotal + $serviceFee) * $taxRate, 2);

        $totalPrice = round($staySubtotal + $cleaningFee + $serviceFee + $taxAmount, 2);

        return [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'nights' => $nights,
            'base_nightly_rate' => $baseNightlyRate,
            'base_subtotal' => round($baseSubtotal, 2),
            'weekend_nights' => $weekendNights,
            'weekend_surcharge' => $weekendSurcharge,
            'pricing_rules' => $pricingAdjustments,
            'stay_subtotal' => $staySubtotal,
            'cleaning_fee' => $cleaningFee,
            'service_fee' => $serviceFee,
            'tax_amount' => $taxAmount,
            'total_price' => $totalPrice,
            'currency' => 'ETB',
            'daily_breakdown' => $dailyBreakdown,
        ];
    }

    /**
     * Bulk confirm bookings.
     */
    public function bulkConfirm(Collection $bookings, ?string $confirmedBy = null): Collection
    {
        return $bookings->map(function ($booking) use ($confirmedBy) {
            return $this->confirm($booking, $confirmedBy);
        });
    }

    /**
     * Bulk cancel bookings.
     */
    public function bulkCancel(Collection $bookings, ?string $cancelledBy = null, string $reason = 'admin_cancelled'): Collection
    {
        return $bookings->map(function ($booking) use ($cancelledBy, $reason) {
            return $this->cancel($booking, $cancelledBy, $reason);
        });
    }
}
