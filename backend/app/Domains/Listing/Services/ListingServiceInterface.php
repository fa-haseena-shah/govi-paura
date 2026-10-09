<?php

namespace App\Domains\Listing\Services;

use App\Domains\Listing\DTOs\CreateListingRequestData;
use App\Domains\Listing\DTOs\FilterListingData;
use App\Domains\Listing\DTOs\ListingResponseData;
use App\Domains\Listing\DTOs\UpdateListingRequestData;

interface ListingServiceInterface
{
    public function create(CreateListingRequestData $data): ListingResponseData;

    public function update(UpdateListingRequestData $data): ListingResponseData;

    public function close(int $listingId, int $farmerId): ListingResponseData;

    public function viewDetails(int $listingId): ListingResponseData;

    /** @return array{data: ListingResponseData[], total: int, page: int, perPage: int} */
    public function browse(FilterListingData $filter): array;

    /** All of one farmer's listings, whatever their status. @return ListingResponseData[] */
    public function forFarmer(int $farmerId): array;

    public function reduceStock(int $listingId, int $quantityPurchased): void;
}