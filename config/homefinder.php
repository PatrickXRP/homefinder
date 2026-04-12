<?php

return [
    'anthropic_key' => env('ANTHROPIC_API_KEY'),
    'anthropic_model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
    'family' => [
        'name' => 'Leegte',
        'adults' => ['Patrick', 'Nathalie'],
        'children' => [
            ['name' => 'Naomi', 'emoji' => '👧', 'age' => 11],
            ['name' => 'Sam', 'emoji' => '🧒', 'age' => 10],
            ['name' => 'Zoe', 'emoji' => '👧', 'age' => 8],
        ],
        'base_location' => 'Nomadisch (nu: Tarragona, Spanje)',
    ],
];
