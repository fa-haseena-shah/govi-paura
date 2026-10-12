<?php

return [

    'currency' => 'LKR',

    // riders get a reminder this many days before their subscription ends
    'reminder_days' => 3,

    // plans rider can choose from
    'plans' => [
        'basic' => [
            'name' => 'Monthly plan',
            'months' => 1,
            'fee' => 1500.00,
            'description' => 'Subscribing to this plan allows you to deliver for Govi Paura for one month.',
        ],
    ],

];
