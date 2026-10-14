<?php

namespace App\Domains\Delivery\DTOs;

use App\Domains\Delivery\Models\Delivery;
use App\Enums\DeliveryStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Models\User;

/**
 * One delivery as the API returns it. What a caller sees depends on who they are:
 *  - the assigned rider, the order's farmer and buyer, and admins get everything
 *  - any other rider browsing an open delivery gets produce, region, distance and the SLA, but
 *    no names, phone numbers or street addresses, and map pins rounded to ~1 km
 */
final readonly class DeliveryData
{
    public const RELATIONS = [
        'order.items.listing',
        'order.buyer.buyerProfile',
        'order.farmer.farmerProfile',
        'order.payments',
        'riderUser.riderProfile.vehicles',
        'logs',
    ];

    public function __construct(
        public int $id,
        public int $orderId,
        public string $status,
        public string $statusLabel,
        public ?string $nextStatus,
        public ?string $category,
        public ?string $region,
        public array $produce,
        public float $totalQuantity,
        public array $pickup,
        public array $dropoff,
        public ?float $distanceKm,
        public ?int $estimatedMinutes,
        public array $sla,
        public ?array $cashOnDelivery,
        public ?array $rider,
        public array $stages,
        public array $timeline,
        public bool $claimable,
    ) {}

    public static function fromModel(Delivery $delivery, User $viewer): self
    {
        $delivery->loadMissing(self::RELATIONS);

        $order = $delivery->order;
        $full = self::hasFullAccess($delivery, $viewer) || $viewer->isRider();

        $produce = $order->items->map(fn ($item) => [
            'listingId' => $item->listing_id,
            'cropType' => $item->listing?->crop_type,
            'quantity' => (int) $item->qty,
            'unit' => 'kg',
        ])->values()->all();

        return new self(
            id: $delivery->id,
            orderId: $delivery->order_id,
            status: $delivery->status->value,
            statusLabel: $delivery->status->label(),
            nextStatus: $delivery->status->next()?->value,
            category: $delivery->category?->value,
            region: $delivery->region,
            produce: $produce,
            totalQuantity: (float) $order->items->sum('qty'),
            pickup: self::place(
                $full, $delivery->pickup, $delivery->pickup_lat, $delivery->pickup_lng,
                $order->farmer?->full_name, null, $order->farmer?->phone,
            ),
            dropoff: self::place(
                $full, $delivery->dropoff, $delivery->dropoff_lat, $delivery->dropoff_lng,
                $order->buyer?->full_name, $order->buyer?->buyerProfile?->business_name, $order->buyer?->phone,
            ),
            distanceKm: $delivery->distance_km,
            estimatedMinutes: $delivery->estimated_minutes,
            sla: self::sla($delivery),
            cashOnDelivery: $full ? self::cashOnDelivery($delivery) : null,
            rider: self::rider($delivery, $viewer),
            stages: self::stages($delivery),
            timeline: $delivery->logs->map(fn ($log) => [
                'status' => $log->status->value,
                'label' => $log->status->label(),
                'at' => $log->status_timestamp->toIso8601String(),
                'note' => $log->note,
            ])->values()->all(),
            claimable: $delivery->status === DeliveryStatus::Pending && $delivery->rider_id === null,
        );
    }

    /** Who may see names, phones, street addresses and exact coordinates. */
    public static function hasFullAccess(Delivery $delivery, User $viewer): bool
    {
        return $viewer->isAdmin()
            || $delivery->rider_id === $viewer->id
            || $delivery->order->buyer_id === $viewer->id
            || $delivery->order->farmer_id === $viewer->id;
    }

    private static function place(bool $full, ?string $address, ?float $lat, ?float $lng, ?string $name, ?string $business, ?string $phone): array
    {
        $place = [
            'latitude' => $lat === null ? null : ($full ? $lat : round($lat, 2)),
            'longitude' => $lng === null ? null : ($full ? $lng : round($lng, 2)),
            'approximate' => !$full,
        ];

        if (!$full) {
            return $place + ['address' => null, 'contactName' => null, 'businessName' => null, 'contactPhone' => null];
        }

        return $place + [
            'address' => $address,
            'contactName' => $name,
            'businessName' => $business,
            'contactPhone' => $phone,
        ];
    }

    private static function sla(Delivery $delivery): array
    {
        $delivered = $delivery->status === DeliveryStatus::Delivered;
        $remaining = $delivered ? null : $delivery->minutesRemaining();

        return [
            'hours' => (int) config('delivery.sla_hours'),
            'orderPlacedAt' => $delivery->order->created_at->toIso8601String(),
            'deadlineAt' => $delivery->deadline_at?->toIso8601String(),
            'minutesRemaining' => $remaining,
            'atRisk' => $remaining !== null && $remaining <= (int) config('delivery.at_risk_minutes'),
            'breached' => $remaining !== null && $remaining < 0,
            'deliveredLate' => $delivered && $delivery->deadline_at !== null && $delivery->delivered_at?->gt($delivery->deadline_at) === true,
            'pickupDueAt' => $delivery->status === DeliveryStatus::Assigned ? $delivery->pickup_due_at?->toIso8601String() : null,
        ];
    }

    // cash the rider has to take at the door; only shown for cash-on-delivery orders still unpaid
    private static function cashOnDelivery(Delivery $delivery): ?array
    {
        $payment = $delivery->order->payments->sortByDesc('id')->first();

        if ($payment === null || $payment->method !== PaymentMethod::CashOnDelivery) {
            return null;
        }

        return [
            'amountToCollect' => (float) $payment->amount,
            'currency' => 'LKR',
            'collected' => $payment->status === PaymentTransactionStatus::Success,
        ];
    }

    private static function rider(Delivery $delivery, User $viewer): ?array
    {
        $rider = $delivery->riderUser;

        // an admin or the farmer / buyer sees who is carrying the order; other riders never do
        if ($rider === null || $viewer->isRider() && $viewer->id !== $rider->id) {
            return null;
        }

        return [
            'id' => $rider->id,
            'name' => $rider->full_name,
            'phone' => $rider->phone,
            'vehicles' => $rider->riderProfile?->vehicles->map(fn ($v) => [
                'type' => $v->vehicle_type->value,
                'number' => $v->vehicle_no,
            ])->values()->all() ?? [],
        ];
    }

    // the Assigned -> Picked Up -> In Transit -> Delivered stepper
    private static function stages(Delivery $delivery): array
    {
        $reached = [
            DeliveryStatus::Assigned->value => $delivery->assigned_at,
            DeliveryStatus::PickedUp->value => $delivery->picked_up_at,
            DeliveryStatus::InTransit->value => $delivery->in_transit_at,
            DeliveryStatus::Delivered->value => $delivery->delivered_at,
        ];

        $stages = [];

        foreach ([DeliveryStatus::Assigned, DeliveryStatus::PickedUp, DeliveryStatus::InTransit, DeliveryStatus::Delivered] as $stage) {
            $at = $reached[$stage->value];

            $stages[] = [
                'status' => $stage->value,
                'label' => $stage->label(),
                'done' => $at !== null,
                'current' => $delivery->status === $stage,
                'reachedAt' => $at?->toIso8601String(),
            ];
        }

        return $stages;
    }
}
