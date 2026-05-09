<?php

namespace App\Services\Riot;

use Illuminate\Support\Facades\Http;

class RiotApiClient
{
    private string $apiKey;

    private string $baseUrlRouting;

    private string $baseUrlPlatform;

    private int $timeout;

    private int $numberOfMatches;

    public function __construct()
    {
        $this->apiKey = config('riot.api_key');
        $this->baseUrlRouting = config('riot.base_urls.routing'); // https://europe.api.riotgames.com
        $this->baseUrlPlatform = config('riot.base_urls.platform'); // https://euw1.api.riotgames.com
        $this->timeout = config('riot.timeout');
        $this->numberOfMatches = config('riot.number_of_matches');
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

    public function getMatchId(string $puuid, ?int $count = null): ?array
    {
        $url = "{$this->baseUrlRouting}/lol/match/v5/matches/by-puuid/{$puuid}/ids";
        $response = Http::withHeaders([
            'X-Riot-Token' => $this->apiKey,
        ])->timeout($this->timeout)->get($url, [
            'type' => 'ranked',
            'count' => $count ?? $this->numberOfMatches,
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function getMatchDetail(string $matchId): ?array
    {
        $url = "{$this->baseUrlRouting}/lol/match/v5/matches/{$matchId}";
        $response = Http::withHeaders([
            'X-Riot-Token' => $this->apiKey,
        ])->timeout($this->timeout)
            ->get($url);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function getRecentMatches(string $puuid, ?int $count = null): array
    {
        $matchesIds = $this->getMatchId($puuid, $count ?? $this->numberOfMatches) ?? [];
        $matches = [];
        foreach ($matchesIds as $matchId) {
            $matchData = $this->getMatchDetail($matchId);

            if (! $matchData) {
                continue;
            }

            $userDataOfMatch = collect($matchData['info']['participants'])
                ->firstWhere('puuid', $puuid);

            if (! $userDataOfMatch) {
                continue;
            }

            $matches[] = [
                'match_id' => $matchId,
                'game_duration' => $matchData['info']['gameDuration'],
                'played_at' => date('Y-m-d H:i:s', $matchData['info']['gameStartTimestamp'] / 1000),
                'champion_name' => $userDataOfMatch['championName'],
                'champion_id' => $userDataOfMatch['championId'],
                'champion_level' => $userDataOfMatch['champLevel'],
                'role' => $userDataOfMatch['teamPosition'],
                'win' => $userDataOfMatch['win'],
                'kills' => $userDataOfMatch['kills'],
                'deaths' => $userDataOfMatch['deaths'],
                'assists' => $userDataOfMatch['assists'],
                'cs' => $userDataOfMatch['totalMinionsKilled'] + $userDataOfMatch['neutralMinionsKilled'],
                'items' => [
                    $userDataOfMatch['item0'],
                    $userDataOfMatch['item1'],
                    $userDataOfMatch['item2'],
                    $userDataOfMatch['item3'],
                    $userDataOfMatch['item4'],
                    $userDataOfMatch['item5'],
                    $userDataOfMatch['item6'],
                ],
            ];
        }

        return $matches;
    }
}
