<?php

namespace App\Domains\Bidding\Services;

use App\Domains\Bidding\DTOs\BidDTO;
use App\Domains\Bidding\DTOs\BidFilterDTO;
use App\Domains\Bidding\DTOs\BidPageDTO;
use App\Domains\Bidding\DTOs\BidSummaryDTO;
use App\Domains\Bidding\DTOs\RejectBidDTO;
use App\Domains\Bidding\DTOs\SubmitBidDTO;

interface BidServiceInterface
{
    /** Buyer submits a bid on a timed-bidding listing. */
    public function submitBid(SubmitBidDTO $dto): BidDTO;

    /** Current highest bid, bid count and minimum next bid for a listing. */
    public function getListingBidSummary(int $listingId): BidSummaryDTO;

    /** Farmer views every bid (full history, all statuses) on one of their listings. */
    public function getBidsForListing(int $listingId, int $farmerId, BidFilterDTO $filter): BidPageDTO;

    /** Buyer views their own bids and tracks each one's status. */
    public function getBuyerBids(int $buyerId, BidFilterDTO $filter): BidPageDTO;

    /** A single bid, visible to the bidder or the listing's farmer only. */
    public function getBid(int $bidId, int $userId): BidDTO;

    /** Farmer accepts a pending bid. */
    public function acceptBid(int $bidId, int $farmerId): BidDTO;

    /** Farmer rejects a pending bid during the bidding period. */
    public function rejectBid(RejectBidDTO $dto): BidDTO;
}
