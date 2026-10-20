<?php

namespace App\Domains\Delivery\Services;

use App\Domains\Delivery\DTOs\DeliveryData;
use App\Domains\Delivery\Models\Delivery;
use App\Domains\Order\Models\Order;
use App\Enums\DeliveryStatus;
use App\Models\User;

/**
 * Exceptions: InvalidArgumentException = not found (404), DeliveryAccessException = not allowed (403),
 * RuntimeException = the delivery is in the wrong state for this (409).
 */
interface DeliveryServiceInterface
{
    /** Creates the delivery for an order (once) and starts the 12-hour clock from order placement. */
    public function ensureForOrder(Order $order): Delivery;

    /* ----- riders ----- */

    /** 8.1: open deliveries, most urgent first. @return array{data: DeliveryData[], total: int, page: int, perPage: int, region: ?string} */
    public function opportunities(User $rider, ?string $region, int $page, int $perPage): array;

    /** 8.1: take an open delivery. */
    public function claim(User $rider, int $deliveryId): DeliveryData;

    /** Hand a delivery back before pick-up. */
    public function release(User $rider, int $deliveryId, ?string $reason): DeliveryData;

    /** 8.2 @return DeliveryData[] */
    public function assigned(User $rider): array;

    /** 8.7 @return array{data: DeliveryData[], total: int, page: int, perPage: int, summary: array} */
    public function history(User $rider, int $page, int $perPage): array;

    /** 8.5 */
    public function updateStatus(User $rider, int $deliveryId, DeliveryStatus $newStatus, ?string $note): DeliveryData;

    /* ----- everyone ----- */

    /** 8.3 + 8.4 */
    public function show(User $viewer, int $deliveryId): DeliveryData;

    /** 8.3: Leaflet-ready payload. */
    public function map(User $viewer, int $deliveryId): array;

    /* ----- farmers and buyers ----- */

    /** 8.6 */
    public function trackOrder(User $viewer, int $orderId): DeliveryData;

    /** 8.6 @return DeliveryData[] */
    public function trackingFor(User $user): array;

    /* ----- admin ----- */

    /** @return array{data: DeliveryData[], total: int, page: int, perPage: int} */
    public function adminList(User $admin, ?string $status, bool $atRiskOnly, int $page, int $perPage): array;

    /* ----- locations ----- */

    /** @return array{address: ?string, region: ?string, latitude: ?float, longitude: ?float} */
    public function getLocation(User $user): array;

    /** Saves the farmer's pickup / buyer's drop-off location and refreshes deliveries not yet picked up. */
    public function saveLocation(User $user, array $data): array;

    /* ----- scheduled ----- */

    /** Backfill, auto-release, at-risk and breach checks. @return array<string,int> */
    public function enforceSla(): array;
}
