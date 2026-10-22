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
            $accommodation = Accommodation::findOrFail($data['accommodation_id']);

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
     * Confirm a pending booking (host action).
     */
    public function confirm(AccommodationBooking $booking, ?string $confirmedBy = null): AccommodationBooking
    {
        return DB::transaction(function () use ($booking, $confirmedBy) {
            if ($booking->status !== 'pending') {
                throw new \InvalidArgumentException('Only pending bookings can be confirmed');
            }

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
            if (! in_array($booking->status, ['pending', 'confirmed'])) {
                throw new \InvalidArgumentException('Only pending or confirmed bookings can be cancelled');
            }

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
        // Check availability blocks
        $conflict = AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where('start_date', '<', $checkOut)
            ->where('end_date', '>', $checkIn)
            ->exists();

        if ($conflict) {
            throw new \DomainException('The selected dates are not available');
        }

        // Check existing confirmed/pending bookings
        $bookingConflict = AccommodationBooking::where('accommodation_id', $accommodation->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->exists();

        if ($bookingConflict) {
            throw new \DomainException('The selected dates are already booked');
        }
    }

    /**
     * Calculate total price based on base price and pricing rules.
     */
    public function calculatePrice(Accommodation $accommodation, string $checkIn, string $checkOut): float
    {
        $basePrice = $accommodation->listing->price ?? $accommodation->property->price ?? 100;
        $days = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
        $totalPrice = $basePrice * $days;

        $rules = AccommodationPricingRule::where('accommodation_id', $accommodation->id)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where(function ($q2) use ($checkIn, $checkOut) {
                    $q2->whereNull('start_date')
                        ->orWhere(function ($q3) use ($checkIn, $checkOut) {
                            $q3->where('start_date', '<', $checkOut)
                                ->where('end_date', '>', $checkIn);
                        });
                });
            })
            ->orderByDesc('priority')
            ->get();

        if ($rules->isNotEmpty()) {
            $rule = $rules->first();
            if ($rule->price_modifier_type === 'fixed') {
                $totalPrice = $rule->price_modifier_value * $days;
            } else {
                $totalPrice = $totalPrice * (1 + ($rule->price_modifier_value / 100));
            }
        }

        return $totalPrice;
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

        // Day by day breakdown to analyze weekends
        $dailyBreakdown = [];
        $weekendNights = 0;
        $current = $start->copy();

        while ($current->lt($end)) {
            // Friday (5) and Saturday (6) considered weekend nights
            $isWeekend = in_array($current->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY]);
            if ($isWeekend) {
                $weekendNights++;
            }
            $dailyBreakdown[] = [
                'date' => $current->toDateString(),
                'day_of_week' => $current->format('l'),
                'is_weekend' => $isWeekend,
                'rate' => $baseNightlyRate,
            ];
            $current->addDay();
        }

        // Weekend surcharge (default 10% on weekend nights if option enabled or rules dictate)
        $weekendMultiplier = $options['weekend_multiplier'] ?? 0.10;
        $weekendSurcharge = round($weekendNights * $baseNightlyRate * $weekendMultiplier, 2);

        // Fetch applicable accommodation pricing rules
        $rules = AccommodationPricingRule::where('accommodation_id', $accommodation->id)
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where(function ($q2) use ($checkIn, $checkOut) {
                    $q2->whereNull('start_date')
                        ->orWhere(function ($q3) use ($checkIn, $checkOut) {
                            $q3->where('start_date', '<', $checkOut)
                                ->where('end_date', '>', $checkIn);
                        });
                });
            })
            ->orderByDesc('priority')
            ->get();

        $pricingAdjustments = [];
        $ruleAdjustmentTotal = 0.0;

        foreach ($rules as $rule) {
            $adjAmount = 0.0;
            if ($rule->price_modifier_type === 'fixed') {
                $adjAmount = ($rule->price_modifier_value * $nights) - $baseSubtotal;
            } else {
                $adjAmount = $baseSubtotal * ($rule->price_modifier_value / 100);
            }
            $ruleAdjustmentTotal += $adjAmount;
            $pricingAdjustments[] = [
                'rule_id' => $rule->id,
                'type' => $rule->type,
                'modifier_type' => $rule->price_modifier_type,
                'modifier_value' => (float) $rule->price_modifier_value,
                'adjustment_amount' => round($adjAmount, 2),
            ];
        }

        $staySubtotal = round($baseSubtotal + $weekendSurcharge + $ruleAdjustmentTotal, 2);

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
