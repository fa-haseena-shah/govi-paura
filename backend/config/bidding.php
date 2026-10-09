<?php

return [

    /*
    | Minimum amount (LKR per kg) a new bid must exceed the current highest
    | non-rejected bid by. The first bid on a listing only has to meet the
    | listing's starting price.
    */
    'min_increment' => (float) env('BIDDING_MIN_INCREMENT', 5.00),

    /*
    | Eloquent model of the Listing domain. Bidding reads these columns from it:
    |   id, farmer_id, selling_method, status, starting_price,
    |   closing_time, qty
    | If your Listing model lives elsewhere, change this ONE line.
    */
    'listing_model' => \App\Domains\Listing\Models\Listing::class,

    'default_per_page' => 15,
    'max_per_page' => 100,
];
