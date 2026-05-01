<?php

$regionRouting = env('RIOT_API_REGION_ROUTING', 'europe');
$regionPlatform = env('RIOT_API_REGION_PLATFORM', 'euw1');

return [
    'api_key' => env('RIOT_API_KEY'),
    'region_routing' => $regionRouting,
    'region_platform' => $regionPlatform,
    'base_urls' => [
        'routing' => "https://{$regionRouting}.api.riotgames.com",
        'platform' => "https://{$regionPlatform}.api.riotgames.com",
    ],
    'timeout' => 5,
];
