<?php

return [

    /*
    | The promise: produce reaches the buyer within this many hours of the ORDER being placed.
    | The clock starts at orders.created_at (not when a rider accepts), so waiting for a rider
    | eats into the same budget.
    */
    'sla_hours' => (int) env('DELIVERY_SLA_HOURS', 12),

    /*
    | A rider who claims a delivery must pick it up within this many minutes (capped at the
    | deadline). Otherwise the claim is released back to the pool by deliveries:enforce-sla so
    | one idle rider cannot burn the 12 hours.
    */
    'pickup_window_minutes' => (int) env('DELIVERY_PICKUP_WINDOW_MINUTES', 120),

    // Not delivered and this close to the deadline (or past it) = "at risk"; admins are alerted once.
    'at_risk_minutes' => (int) env('DELIVERY_AT_RISK_MINUTES', 180),

    // A claim is refused when estimated travel time + this buffer is longer than the time left.
    'min_claim_buffer_minutes' => (int) env('DELIVERY_MIN_CLAIM_BUFFER_MINUTES', 30),

    // Travel estimate: straight-line distance x road_factor / avg_speed_kmh.
    'avg_speed_kmh' => (float) env('DELIVERY_AVG_SPEED_KMH', 30),
    'road_factor' => (float) env('DELIVERY_ROAD_FACTOR', 1.3),

    // How many unfinished deliveries one rider can hold at once.
    'max_active_per_rider' => (int) env('DELIVERY_MAX_ACTIVE_PER_RIDER', 3),

    /*
    | true  = riders only see/claim a delivery once its order is past "pending"
    |         (the buyer has paid by card or chosen cash on delivery), so nobody rides for an
    |         order that may never be paid. The 12h clock still runs from order placement.
    | false = riders can claim as soon as the order exists.
    */
    'require_confirmed_order' => (bool) env('DELIVERY_REQUIRE_CONFIRMED_ORDER', true),

    // Leaflet / OpenStreetMap tile settings returned by the map endpoint.
    'map' => [
        'tile_url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '&copy; OpenStreetMap contributors',
    ],

    // Sri Lanka bounding box, used to reject obviously wrong coordinates.
    'bounds' => [
        'lat' => [5.7, 10.0],
        'lng' => [79.4, 82.0],
    ],

    'default_per_page' => 15,
    'max_per_page'     => 50,

    /*
    | Delivery fee paid to the rider.
    | fee_lkr = fee_rate_per_km × distance_km  (rounded to 2 d.p.)
    | When a delivery turns at-risk the fee is multiplied by at_risk_fee_multiplier
    | and stored in at_risk_fee_lkr so the original fee is never overwritten.
    */
    'fee_rate_per_km' => (float) env('DELIVERY_FEE_RATE_PER_KM', 5.0),
    'at_risk_fee_multiplier' => (float) env('DELIVERY_AT_RISK_FEE_MULTIPLIER', 1.5),
];

