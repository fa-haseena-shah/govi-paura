<?php

namespace App\Console\Commands;

use App\Domains\Listing\Models\Listing;
use App\Enums\ListingStatus;
use App\Enums\SellingMethod;
use App\Domains\Listing\Events\BiddingClosed;
use Illuminate\Console\Command;

class CloseExpiredBiddingListings extends Command
{
    protected $signature = 'listings:close-expired-bidding';
    protected $description = 'Close bidding listings whose closing time has passed';

    public function handle(): int
    {
        $listings = Listing::query()
            ->where('selling_method', SellingMethod::Bidding->value)
            ->where('status', ListingStatus::Active->value)
            ->where('closing_time', '<=', now())
            ->get();

        foreach ($listings as $listing) {
            $listing->update(['status' => ListingStatus::Closed]);
            BiddingClosed::dispatch($listing);
        }

        $this->info("Closed {$listings->count()} expired bidding listing(s).");

        return self::SUCCESS;
    }
}