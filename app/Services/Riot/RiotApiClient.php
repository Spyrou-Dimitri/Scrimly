<?php

namespace App\Services\Riot;

use Illuminate\Support\Facades\Http;

class RiotApiClient
{
    private string $apiKey;

    private string $baseUrlRouting;

    private string $baseUrlPlatform;

    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('riot.api_key');
        $this->baseUrlRouting = config('riot.base_urls.routing');
        $this->baseUrlPlatform = config('riot.base_urls.platform');
        $this->timeout = config('riot.timeout');
    }

    public function getAccount(string $gameName, string $tagLine): ?array
    {
        $response = Http::withHeaders([
            'X-Riot-Token' => $this->apiKey,
        ])->timeout($this->timeout)->get(
            $this->baseUrlRouting.'/riot/account/v1/accounts/by-riot-id/'.$gameName.'/'.$tagLine
        );

        if (! $response->successful()) {
            return null;
        }

        $accountData = $response->json();

        return [
            'puuid' => $accountData['puuid'],
            'gameName' => $accountData['gameName'],
            'tagLine' => $accountData['tagLine'],
            'soloQueue' => $this->getSoloQueueRank($accountData['puuid']),
        ];
    }

    /**
     * @return array{tier: string, rank: string, leaguePoints: int, wins: int, losses: int}|null
     */
    public function getSoloQueueRank(string $puuid): ?array
    {
        $response = Http::withHeaders([
            'X-Riot-Token' => $this->apiKey,
        ])->timeout($this->timeout)->get(
            $this->baseUrlPlatform.'/lol/league/v4/entries/by-puuid/'.$puuid
        );

        if (! $response->successful()) {
            return null;
        }

        $soloQueue = collect($response->json())
            ->firstWhere('queueType', 'RANKED_SOLO_5x5');

        if (! $soloQueue) {
            return null;
        }

        return [
            'tier' => $soloQueue['tier'],
            'rank' => $soloQueue['rank'],
            'leaguePoints' => $soloQueue['leaguePoints'],
            'wins' => $soloQueue['wins'],
            'losses' => $soloQueue['losses'],
        ];
    }
}
