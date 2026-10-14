<?php

namespace App\Domains\Delivery\Services;

use App\Domains\Auth\Models\Rider;
use App\Domains\Delivery\DTOs\DeliveryData;
use App\Domains\Delivery\Events\DeliveryStatusChanged;
use App\Domains\Delivery\Exceptions\DeliveryAccessException;
use App\Domains\Delivery\Models\Delivery;
use App\Domains\Delivery\Models\DeliveryStatusLog;
use App\Domains\Delivery\Support\Geo;
use App\Domains\Notification\Services\NotificationServiceInterface;
use App\Domains\Order\Events\OrderStatusChanged;
use App\Domains\Order\Models\Order;
use App\Domains\Subscription\Services\SubscriptionServiceInterface;
use App\Enums\DeliveryCategory;
use App\Enums\DeliveryStatus;
use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class DeliveryService implements DeliveryServiceInterface
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptions,
        private readonly NotificationServiceInterface $notifications,
    ) {}

    /* ====================================================================
     | Creating the delivery (starts the 12-hour clock)
     ==================================================================== */

    public function ensureForOrder(Order $order): Delivery
    {
        $existing = Delivery::where('order_id', $order->id)->first();

        if ($existing !== null) {
            return $existing;
        }

        $order->loadMissing(['items.listing', 'farmer.farmerProfile', 'buyer.buyerProfile']);

        $farmer = $order->farmer?->farmerProfile;
        $buyer = $order->buyer?->buyerProfile;

        $pickupLat = $this->coordinate($farmer?->latitude);
        $pickupLng = $this->coordinate($farmer?->longitude);
        $dropoffLat = $this->coordinate($buyer?->latitude);
        $dropoffLng = $this->coordinate($buyer?->longitude);

        [$km, $minutes] = Geo::estimate($pickupLat, $pickupLng, $dropoffLat, $dropoffLng);

        $slaHours = (int) config('delivery.sla_hours');

        try {
            return DB::transaction(function () use ($order, $farmer, $buyer, $pickupLat, $pickupLng, $dropoffLat, $dropoffLng, $km, $minutes, $slaHours) {
                $delivery = Delivery::create([
                    'order_id' => $order->id,
                    'status' => DeliveryStatus::Pending,
                    'category' => $this->categoryFor($order),
                    'region' => $this->clean($farmer?->region) ?? $this->clean($buyer?->region),
                    'pickup' => $farmer?->address ?? $order->items->first()?->listing?->location ?? 'Pickup address not provided',
                    'dropoff' => $buyer?->address ?? 'Delivery address not provided',
                    'pickup_lat' => $pickupLat,
                    'pickup_lng' => $pickupLng,
                    'dropoff_lat' => $dropoffLat,
                    'dropoff_lng' => $dropoffLng,
                    'distance_km' => $km,
                    'estimated_minutes' => $minutes,
                    // the promise runs from the moment the ORDER was placed
                    'deadline_at' => $order->created_at->copy()->addHours($slaHours),
                ]);

                $this->log($delivery, DeliveryStatus::Pending, "Order placed. Must reach the buyer within {$slaHours} hours.", null);

                return $delivery;
            });
        } catch (QueryException $e) {
            // unique(order_id): a parallel request created it first
            $existing = Delivery::where('order_id', $order->id)->first();

            if ($existing === null) {
                throw $e;
            }

            return $existing;
        }
    }

    /* ====================================================================
     | Riders: 8.1 opportunities + claim
     ==================================================================== */

    public function opportunities(User $riderUser, ?string $region, int $page, int $perPage): array
    {
        $rider = $this->riderProfile($riderUser);
        $effectiveRegion = $this->effectiveRegion($rider, $region);

        $query = Delivery::query()
            ->where('status', DeliveryStatus::Pending->value)
            ->whereNull('rider_id')
            ->where(fn ($q) => $q->where('category', $rider->category->value)->orWhereNull('category'));

        if ($effectiveRegion !== null) {
            // deliveries with no region on file are shown to everyone so they cannot be forgotten
            $query->where(fn ($q) => $q
                ->whereRaw('LOWER(region) = ?', [Str::lower($effectiveRegion)])
                ->orWhereNull('region'));
        }

        if (config('delivery.require_confirmed_order')) {
            $query->whereHas('order', fn ($q) => $q->where('status', '!=', OrderStatus::Pending->value));
        }

        $paginator = $query
            ->with(DeliveryData::RELATIONS)
            ->orderBy('deadline_at')   // most urgent first
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()->map(fn (Delivery $d) => DeliveryData::fromModel($d, $riderUser))->values()->all(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'region' => $effectiveRegion,
        ];
    }

    public function claim(User $riderUser, int $deliveryId): DeliveryData
    {
        $rider = $this->riderProfile($riderUser);

        if (!$this->subscriptions->hasActiveSubscription($riderUser->id)) {
            throw new RuntimeException('You need an active subscription to take deliveries.');
        }

        DB::transaction(function () use ($riderUser, $rider, $deliveryId) {
            // lock the delivery so two riders can never both win it
            $delivery = Delivery::whereKey($deliveryId)->lockForUpdate()->first();

            if ($delivery === null) {
                throw new InvalidArgumentException('Delivery not found.');
            }

            if ($delivery->status !== DeliveryStatus::Pending || $delivery->rider_id !== null) {
                throw new RuntimeException('This delivery has already been taken by another rider.');
            }

            $delivery->loadMissing('order');

            if (config('delivery.require_confirmed_order') && $delivery->order->status === OrderStatus::Pending) {
                throw new RuntimeException('This order is not confirmed yet. It opens for riders once the buyer pays or chooses cash on delivery.');
            }

            if ($delivery->category !== null && $delivery->category !== $rider->category) {
                throw new DeliveryAccessException("This delivery is for {$delivery->category->value} riders.");
            }

            $own = $this->clean($rider->region);

            if ($own !== null && $delivery->region !== null && Str::lower($delivery->region) !== Str::lower($own)) {
                throw new DeliveryAccessException('This delivery is outside your region.');
            }

            // serialise this rider's claims so the active-delivery limit cannot be beaten by two quick taps
            Rider::whereKey($rider->user_id)->lockForUpdate()->first();

            $active = Delivery::where('rider_id', $riderUser->id)
                ->whereIn('status', array_map(fn (DeliveryStatus $s) => $s->value, DeliveryStatus::active()))
                ->count();

            $max = (int) config('delivery.max_active_per_rider');

            if ($active >= $max) {
                throw new RuntimeException("You already hold {$active} unfinished deliveries (limit {$max}). Finish one before taking another.");
            }

            // refuse a delivery that physically cannot make the deadline any more
            $remaining = $delivery->minutesRemaining();

            if ($remaining !== null && $remaining > 0 && $delivery->estimated_minutes !== null) {
                $needed = $delivery->estimated_minutes + (int) config('delivery.min_claim_buffer_minutes');

                if ($needed > $remaining) {
                    throw new RuntimeException("Not enough time left: about {$needed} minutes are needed but only {$remaining} remain before the deadline.");
                }
            }

            $now = now();
            $pickupDue = $now->copy()->addMinutes((int) config('delivery.pickup_window_minutes'));

            if ($delivery->deadline_at !== null && $delivery->deadline_at->gt($now) && $pickupDue->gt($delivery->deadline_at)) {
                $pickupDue = $delivery->deadline_at->copy();
            }

            $delivery->update([
                'rider_id' => $riderUser->id,
                'status' => DeliveryStatus::Assigned,
                'assigned_at' => $now,
                'pickup_due_at' => $pickupDue,
            ]);

            $this->log($delivery, DeliveryStatus::Assigned, 'Claimed by rider.', $riderUser->id);

            DeliveryStatusChanged::dispatch($delivery->id, $delivery->order_id, DeliveryStatus::Assigned->value);
        });

        return DeliveryData::fromModel($this->load($deliveryId), $riderUser);
    }

    public function release(User $riderUser, int $deliveryId, ?string $reason): DeliveryData
    {
        $this->riderProfile($riderUser);

        DB::transaction(function () use ($riderUser, $deliveryId, $reason) {
            $delivery = Delivery::whereKey($deliveryId)->lockForUpdate()->first();

            if ($delivery === null) {
                throw new InvalidArgumentException('Delivery not found.');
            }

            if ($delivery->rider_id !== $riderUser->id) {
                throw new DeliveryAccessException('This delivery is not assigned to you.');
            }

            if ($delivery->status !== DeliveryStatus::Assigned) {
                throw new RuntimeException('Only a delivery you have not picked up yet can be released.');
            }

            $this->returnToPool($delivery, $reason ? "Released by rider: {$reason}" : 'Released by rider.', $riderUser->id);

            DeliveryStatusChanged::dispatch($delivery->id, $delivery->order_id, DeliveryStatus::Pending->value);
        });

        return DeliveryData::fromModel($this->load($deliveryId), $riderUser);
    }

    /* ====================================================================
     | Riders: 8.2 assigned, 8.5 update status, 8.7 history
     ==================================================================== */

    public function assigned(User $riderUser): array
    {
        $this->riderProfile($riderUser);

        return Delivery::with(DeliveryData::RELATIONS)
            ->where('rider_id', $riderUser->id)
            ->whereIn('status', array_map(fn (DeliveryStatus $s) => $s->value, DeliveryStatus::active()))
            ->orderBy('deadline_at')
            ->get()
            ->map(fn (Delivery $d) => DeliveryData::fromModel($d, $riderUser))
            ->values()
            ->all();
    }

    public function updateStatus(User $riderUser, int $deliveryId, DeliveryStatus $newStatus, ?string $note): DeliveryData
    {
        $this->riderProfile($riderUser);

        DB::transaction(function () use ($riderUser, $deliveryId, $newStatus, $note) {
            $delivery = Delivery::whereKey($deliveryId)->lockForUpdate()->first();

            if ($delivery === null) {
                throw new InvalidArgumentException('Delivery not found.');
            }

            if ($delivery->rider_id !== $riderUser->id) {
                throw new DeliveryAccessException('This delivery is not assigned to you.');
            }

            $expected = $delivery->status->next();

            if ($expected === null) {
                throw new RuntimeException("This delivery is already {$delivery->status->label()} and cannot be updated.");
            }

            // stages are strictly ordered: Assigned -> Picked Up -> In Transit -> Delivered
            if ($newStatus !== $expected) {
                throw new RuntimeException("Cannot move from {$delivery->status->label()} to {$newStatus->label()}. The next stage is {$expected->label()}.");
            }

            $now = now();

            $delivery->update(['status' => $newStatus] + match ($newStatus) {
                DeliveryStatus::PickedUp => ['picked_up_at' => $now],
                DeliveryStatus::InTransit => ['in_transit_at' => $now],
                DeliveryStatus::Delivered => ['delivered_at' => $now],
                default => [],
            });

            $this->log($delivery, $newStatus, $note, $riderUser->id);

            if ($newStatus === DeliveryStatus::Delivered) {
                $this->markOrderDelivered($delivery->order_id);
            }

            DeliveryStatusChanged::dispatch($delivery->id, $delivery->order_id, $newStatus->value);
        });

        return DeliveryData::fromModel($this->load($deliveryId), $riderUser);
    }

    public function history(User $riderUser, int $page, int $perPage): array
    {
        $this->riderProfile($riderUser);

        $base = Delivery::where('rider_id', $riderUser->id)->where('status', DeliveryStatus::Delivered->value);

        $total = (clone $base)->count();
        $late = (clone $base)->whereColumn('delivered_at', '>', 'deadline_at')->count();

        $paginator = (clone $base)
            ->with(DeliveryData::RELATIONS)
            ->orderByDesc('delivered_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()->map(fn (Delivery $d) => DeliveryData::fromModel($d, $riderUser))->values()->all(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'summary' => ['completed' => $total, 'onTime' => $total - $late, 'late' => $late],
        ];
    }

    /* ====================================================================
     | Everyone: 8.3 map, 8.4 details
     ==================================================================== */

    public function show(User $viewer, int $deliveryId): DeliveryData
    {
        $delivery = $this->load($deliveryId);
        $this->assertCanView($viewer, $delivery);

        return DeliveryData::fromModel($delivery, $viewer);
    }

    public function map(User $viewer, int $deliveryId): array
    {
        $delivery = $this->load($deliveryId);
        $this->assertCanView($viewer, $delivery);

        $data = DeliveryData::fromModel($delivery, $viewer);

        $pins = [
            ['type' => 'pickup', 'label' => 'Farmer pickup', 'place' => $data->pickup],
            ['type' => 'dropoff', 'label' => 'Buyer drop-off', 'place' => $data->dropoff],
        ];

        $markers = array_map(fn (array $pin) => [
            'type' => $pin['type'],
            'label' => $pin['label'],
            'latitude' => $pin['place']['latitude'],
            'longitude' => $pin['place']['longitude'],
            'address' => $pin['place']['address'],
            'approximate' => $pin['place']['approximate'],
        ], $pins);

        $ready = $markers[0]['latitude'] !== null && $markers[1]['latitude'] !== null;

        return [
            'deliveryId' => $data->id,
            'orderId' => $data->orderId,
            'status' => $data->status,
            'ready' => $ready,   // false until both the farmer and the buyer have saved a location
            'markers' => $markers,
            // Leaflet takes [lat, lng] pairs: L.polyline(route), map.fitBounds(bounds)
            'route' => $ready ? [
                [$markers[0]['latitude'], $markers[0]['longitude']],
                [$markers[1]['latitude'], $markers[1]['longitude']],
            ] : [],
            'bounds' => $ready ? [
                [min($markers[0]['latitude'], $markers[1]['latitude']), min($markers[0]['longitude'], $markers[1]['longitude'])],
                [max($markers[0]['latitude'], $markers[1]['latitude']), max($markers[0]['longitude'], $markers[1]['longitude'])],
            ] : null,
            'center' => $ready ? [
                ($markers[0]['latitude'] + $markers[1]['latitude']) / 2,
                ($markers[0]['longitude'] + $markers[1]['longitude']) / 2,
            ] : null,
            'distanceKm' => $data->distanceKm,
            'estimatedMinutes' => $data->estimatedMinutes,
            'tiles' => config('delivery.map'),
        ];
    }

    /* ====================================================================
     | Farmers and buyers: 8.6 track progress
     ==================================================================== */

    public function trackOrder(User $viewer, int $orderId): DeliveryData
    {
        $delivery = Delivery::with(DeliveryData::RELATIONS)->where('order_id', $orderId)->first();

        if ($delivery === null) {
            throw new InvalidArgumentException('No delivery found for this order.');
        }

        if (!DeliveryData::hasFullAccess($delivery, $viewer) || $viewer->isRider()) {
            throw new DeliveryAccessException('You do not have permission to track this order.');
        }

        return DeliveryData::fromModel($delivery, $viewer);
    }

    public function trackingFor(User $user): array
    {
        return Delivery::with(DeliveryData::RELATIONS)
            ->whereHas('order', fn ($q) => $q->where('buyer_id', $user->id)->orWhere('farmer_id', $user->id))
            ->orderByDesc('id')
            ->get()
            ->map(fn (Delivery $d) => DeliveryData::fromModel($d, $user))
            ->values()
            ->all();
    }

    /* ====================================================================
     | Admin
     ==================================================================== */

    public function adminList(User $admin, ?string $status, bool $atRiskOnly, int $page, int $perPage): array
    {
        $query = Delivery::query();

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($atRiskOnly) {
            $query->where('status', '!=', DeliveryStatus::Delivered->value)
                ->where('deadline_at', '<=', now()->addMinutes((int) config('delivery.at_risk_minutes')));
        }

        $paginator = $query
            ->with(DeliveryData::RELATIONS)
            ->orderBy('deadline_at')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()->map(fn (Delivery $d) => DeliveryData::fromModel($d, $admin))->values()->all(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
        ];
    }

    /* ====================================================================
     | Pickup / drop-off locations
     ==================================================================== */

    public function getLocation(User $user): array
    {
        $profile = $this->locationProfile($user);

        return [
            'role' => $user->role->value,
            'address' => $profile->address,
            'region' => $profile->region,
            'latitude' => $this->coordinate($profile->latitude),
            'longitude' => $this->coordinate($profile->longitude),
        ];
    }

    public function saveLocation(User $user, array $data): array
    {
        $profile = $this->locationProfile($user);

        DB::transaction(function () use ($user, $profile, $data) {
            $profile->update([
                'address' => $data['address'],
                'region' => $data['region'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ]);

            $this->refreshOpenDeliveries($user, $data);
        });

        return $this->getLocation($user->fresh());
    }

    // deliveries nobody has picked up yet pick up the new location; later ones are frozen
    private function refreshOpenDeliveries(User $user, array $data): void
    {
        $isFarmer = $user->isFarmer();

        $deliveries = Delivery::query()
            ->whereIn('status', [DeliveryStatus::Pending->value, DeliveryStatus::Assigned->value])
            ->whereHas('order', fn ($q) => $q->where($isFarmer ? 'farmer_id' : 'buyer_id', $user->id))
            ->lockForUpdate()
            ->get();

        foreach ($deliveries as $delivery) {
            if ($isFarmer) {
                $delivery->fill([
                    'pickup' => $data['address'],
                    'pickup_lat' => $data['latitude'],
                    'pickup_lng' => $data['longitude'],
                    'region' => $this->clean($data['region']),
                ]);
            } else {
                $delivery->fill([
                    'dropoff' => $data['address'],
                    'dropoff_lat' => $data['latitude'],
                    'dropoff_lng' => $data['longitude'],
                    'region' => $delivery->region ?? $this->clean($data['region']),
                ]);
            }

            [$km, $minutes] = Geo::estimate($delivery->pickup_lat, $delivery->pickup_lng, $delivery->dropoff_lat, $delivery->dropoff_lng);
            $delivery->fill(['distance_km' => $km, 'estimated_minutes' => $minutes])->save();
        }
    }

    /* ====================================================================
     | Scheduled: keep the 12-hour promise
     ==================================================================== */

    public function enforceSla(): array
    {
        $counts = ['created' => 0, 'released' => 0, 'at_risk' => 0, 'breached' => 0];

        // 1. an order that somehow has no delivery yet (listener failed, order older than this module)
        $missing = Order::query()
            ->whereNotIn('id', Delivery::query()->select('order_id'))
            ->where('created_at', '>=', now()->subHours((int) config('delivery.sla_hours')))
            ->whereNotIn('status', [OrderStatus::Delivered->value, OrderStatus::Completed->value])
            ->get();

        foreach ($missing as $order) {
            $this->ensureForOrder($order);
            $counts['created']++;
        }

        // 2. a rider claimed it but never picked it up in time: back to the pool
        $stale = Delivery::query()
            ->where('status', DeliveryStatus::Assigned->value)
            ->whereNotNull('pickup_due_at')
            ->where('pickup_due_at', '<', now())
            ->pluck('id');

        foreach ($stale as $id) {
            if ($this->autoRelease((int) $id)) {
                $counts['released']++;
            }
        }

        // 3. getting close to the deadline: tell admins once
        $atRisk = Delivery::query()
            ->with('order')
            ->whereNull('at_risk_at')
            ->where('status', '!=', DeliveryStatus::Delivered->value)
            ->where('deadline_at', '<=', now()->addMinutes((int) config('delivery.at_risk_minutes')))
            ->get();

        foreach ($atRisk as $delivery) {
            $delivery->update(['at_risk_at' => now()]);

            $left = max(0, (int) $delivery->minutesRemaining());
            $state = $delivery->status === DeliveryStatus::Pending ? 'still has no rider' : "is {$delivery->status->label()}";

            $this->notifyAdmins(
                "Delivery at risk: order #{$delivery->order_id}",
                "Order #{$delivery->order_id} {$state} with {$left} minutes left to its deadline.",
                ['order_id' => $delivery->order_id, 'delivery_id' => $delivery->id],
            );

            $counts['at_risk']++;
        }

        // 4. deadline passed
        $breached = Delivery::query()
            ->whereNull('sla_breached_at')
            ->where('status', '!=', DeliveryStatus::Delivered->value)
            ->where('deadline_at', '<', now())
            ->get();

        foreach ($breached as $delivery) {
            $delivery->update(['sla_breached_at' => now()]);

            $this->notifyAdmins(
                "Delivery overdue: order #{$delivery->order_id}",
                "Order #{$delivery->order_id} missed its " . (int) config('delivery.sla_hours') . '-hour delivery deadline and is still ' . strtolower($delivery->status->label()) . '.',
                ['order_id' => $delivery->order_id, 'delivery_id' => $delivery->id],
            );

            $counts['breached']++;
        }

        return $counts;
    }

    private function autoRelease(int $deliveryId): bool
    {
        return DB::transaction(function () use ($deliveryId) {
            $delivery = Delivery::whereKey($deliveryId)->lockForUpdate()->first();

            // re-check under the lock: the rider may have just picked it up
            if ($delivery === null
                || $delivery->status !== DeliveryStatus::Assigned
                || $delivery->pickup_due_at === null
                || $delivery->pickup_due_at->gte(now())) {
                return false;
            }

            $riderId = $delivery->rider_id;

            $this->returnToPool($delivery, 'Released automatically: not picked up within the pickup window.', null);

            if ($riderId !== null) {
                $this->notifications->notify(
                    $riderId,
                    NotificationType::DeliveryStatusUpdated,
                    "Delivery released: order #{$delivery->order_id}",
                    "Order #{$delivery->order_id} was not picked up in time and has been returned to the open list.",
                    ['order_id' => $delivery->order_id, 'delivery_id' => $delivery->id],
                );
            }

            return true;
        });
    }

    /* ====================================================================
     | Helpers
     ==================================================================== */

    private function returnToPool(Delivery $delivery, string $note, ?int $by): void
    {
        $delivery->update([
            'rider_id' => null,
            'status' => DeliveryStatus::Pending,
            'assigned_at' => null,
            'pickup_due_at' => null,
        ]);

        $this->log($delivery, DeliveryStatus::Pending, $note, $by);
    }

    // the rider marking it delivered also moves the order forward (the farmer still completes it)
    private function markOrderDelivered(int $orderId): void
    {
        $order = Order::whereKey($orderId)->lockForUpdate()->first();

        if ($order === null || in_array($order->status, [OrderStatus::Delivered, OrderStatus::Completed], true)) {
            return;
        }

        $previous = $order->status;
        $order->update(['status' => OrderStatus::Delivered]);

        OrderStatusChanged::dispatch($order->fresh(), $previous);
    }

    private function log(Delivery $delivery, DeliveryStatus $status, ?string $note, ?int $by): void
    {
        DeliveryStatusLog::create([
            'delivery_id' => $delivery->id,
            'status_timestamp' => now(),   // microsecond precision, part of the primary key
            'status' => $status,
            'note' => $note,
            'changed_by' => $by,
        ]);
    }

    private function notifyAdmins(string $title, string $message, array $data): void
    {
        $adminIds = User::query()
            ->where('role', UserType::Admin->value)
            ->where('status', UserStatus::Active->value)
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->notifications->notify((int) $adminId, NotificationType::AdminAction, $title, $message, $data);
        }
    }

    private function load(int $deliveryId): Delivery
    {
        $delivery = Delivery::with(DeliveryData::RELATIONS)->find($deliveryId);

        if ($delivery === null) {
            throw new InvalidArgumentException('Delivery not found.');
        }

        return $delivery;
    }

    private function assertCanView(User $viewer, Delivery $delivery): void
    {
        if (DeliveryData::hasFullAccess($delivery, $viewer)) {
            return;
        }

        // any rider who could take an open delivery may look at it (with contact details hidden)
        if ($viewer->isRider() && $delivery->status === DeliveryStatus::Pending) {
            $rider = Rider::find($viewer->id);

            if ($rider !== null && $this->riderMatches($rider, $delivery)) {
                return;
            }
        }

        throw new DeliveryAccessException('You do not have permission to view this delivery.');
    }

    private function riderMatches(Rider $rider, Delivery $delivery): bool
    {
        if ($delivery->category !== null && $delivery->category !== $rider->category) {
            return false;
        }

        $own = $this->clean($rider->region);

        return $own === null || $delivery->region === null || Str::lower($delivery->region) === Str::lower($own);
    }

    private function riderProfile(User $user): Rider
    {
        if (!$user->isRider()) {
            throw new DeliveryAccessException('Only delivery riders can do this.');
        }

        $rider = Rider::find($user->id);

        if ($rider === null) {
            throw new DeliveryAccessException('Rider profile not found.');
        }

        return $rider;
    }

    // a rider with a region on file is bound to it; one without may pick any region to browse
    private function effectiveRegion(Rider $rider, ?string $requested): ?string
    {
        $own = $this->clean($rider->region);
        $requested = $this->clean($requested);

        if ($own === null) {
            return $requested;
        }

        if ($requested !== null && Str::lower($requested) !== Str::lower($own)) {
            throw new DeliveryAccessException("You are registered for the {$own} region and can only see deliveries there.");
        }

        return $own;
    }

    private function locationProfile(User $user): object
    {
        $profile = match (true) {
            $user->isFarmer() => $user->farmerProfile,
            $user->isBuyer() => $user->buyerProfile,
            default => null,
        };

        if ($profile === null) {
            throw new DeliveryAccessException('Only farmers (pickup) and buyers (delivery) have a saved location.');
        }

        return $profile;
    }

    private function categoryFor(Order $order): DeliveryCategory
    {
        $isPaddy = $order->items->contains(
            fn ($item) => preg_match('/paddy|rice/i', (string) $item->listing?->crop_type) === 1
        );

        return $isPaddy ? DeliveryCategory::Paddy : DeliveryCategory::Vegetables;
    }

    private function clean(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }

    private function coordinate(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }
}
