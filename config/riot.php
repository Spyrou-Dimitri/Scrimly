<?php

$regionRouting = env('RIOT_API_REGION_ROUTING', 'europe'); // Europe
$regionPlatform = env('RIOT_API_REGION_PLATFORM', 'euw1'); // EUW1

return [
    'api_key' => env('RIOT_API_KEY'),
    'region_routing' => $regionRouting,
    'region_platform' => $regionPlatform,
    'base_urls' => [
        'routing' => "https://{$regionRouting}.api.riotgames.com", //https://europe.api.riotgames.com
        'platform' => "https://{$regionPlatform}.api.riotgames.com", //https://euw1.api.riotgames.com
    ],
    'timeout' => 5,
    'number_of_matches' => 3, 
    'ddragon_version' => '16.9.1',
];
