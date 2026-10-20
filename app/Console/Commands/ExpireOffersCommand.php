<?php

namespace App\Console\Commands;

use App\Models\Offer;
use App\Models\OfferEvent;
use Illuminate\Console\Command;

class ExpireOffersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'srms:expire-offers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pending offers whose expiration timestamp has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning for expired offers...');

        $expiredOffers = Offer::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = $expiredOffers->count();

        foreach ($expiredOffers as $offer) {
            $offer->update(['status' => 'expired']);

            OfferEvent::create([
                'offer_id' => $offer->id,
                'actor_id' => null, // system actor
                'event_type' => 'expired',
                'amount' => $offer->amount,
                'currency' => 'ETB',
                'notes' => 'Offer expired automatically by scheduler.',
            ]);
        }

        $this->info("Successfully expired {$count} offers.");

        return Command::SUCCESS;
    }
}
