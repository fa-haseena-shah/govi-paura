<?php

namespace App\Console\Commands;

use App\Domains\Listing\Models\Listing;
use App\Enums\ListingStatus;
use App\Enums\SellingMethod;
use Illuminate\Console\Command;

class CloseExpiredBiddingListings extends Command
{
    protected $signature = 'listings:close-expired-bidding';
    protected $description = 'Close bidding listings whose closing time has passed';

    public function handle(): int
    {
        $closed = Listing::query()
            ->where('selling_method', SellingMethod::Bidding->value)
            ->where('status', ListingStatus::Active->value)
            ->where('closing_time', '<=', now())
            ->update(['status' => ListingStatus::Closed->value]);

        $this->info("Closed {$closed} expired bidding listing(s).");

        return self::SUCCESS;
    }
}