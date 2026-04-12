<?php

return [
    'anthropic_key' => env('ANTHROPIC_API_KEY'),
    'anthropic_model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
    'family' => [
        'name' => 'Leegte',
        'adults' => ['Patrick', 'Nathalie'],
        'children' => [
            ['name' => 'Kind 1', 'emoji' => '🧒', 'age' => 11],
            ['name' => 'Kind 2', 'emoji' => '👧', 'age' => 10],
            ['name' => 'Kind 3', 'emoji' => '👧', 'age' => 8],
        ],
        'base_location' => 'Dubai, UAE',
    ],
];
