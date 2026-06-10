<?php

namespace Database\Seeders;

use App\Enums\AbsenceJustification;
use App\Enums\DayOfTheWeek;
use App\Enums\DefaultAvatar;
use App\Enums\DefaultTeam;
use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\LolTier;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\ScrimOutcome;
use App\Enums\StatusInTeam;
use App\Enums\StatusScrim;
use App\Enums\StatusScrimRequest;
use App\Enums\StatusTask;
use App\Enums\TypeScrimGameNote;
use App\Models\Absence;
use App\Models\PlayerDefaultSchedule;
use App\Models\RiotMatch;
use App\Models\RiotProfile;
use App\Models\Scrim;
use App\Models\ScrimGame;
use App\Models\ScrimGameNote;
use App\Models\ScrimGamePlayer;
use App\Models\ScrimRequest;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Riot\RiotApiClient;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    private const TASKS_PER_MEMBER = 4;

    /**
     * @var list<array{title: string, description: string, subtasks: list<string>}>|null
     */
    private ?array $devoirTemplates = null;

    private const RIOT_USERS = [
        ['username' => 'Tokha', 'riot_tag' => 'AmbesseTonFroc#PILOT'],
        ['username' => 'Elise', 'riot_tag' => 'EliseFromWebDev#Web'],
        ['username' => 'Mizuty', 'riot_tag' => 'Mizuty#EUW'],
        ['username' => 'Alucard', 'riot_tag' => 'Alucard#80085'],
        ['username' => 'Pollo', 'riot_tag' => 'PolloO2#2002'],
        ['username' => 'Nekkore', 'riot_tag' => 'Nekkore#Sus'],
        ['username' => 'eNami', 'riot_tag' => 'eNami#NAMI'],
        ['username' => 'mamou', 'riot_tag' => 'mamou#mamo'],
        ['username' => 'Drogas', 'riot_tag' => 'Drogas#1000'],
        ['username' => 'sorrow', 'riot_tag' => 'sorrow#XIII'],
        ['username' => 'Lawin', 'riot_tag' => 'Lawin#2000'],
    ];

    /**
     * Riot IDs des joueurs supplémentaires (référence newplayer.md) : pseudo = partie avant '#'.
     *
     * @var list<string>
     */
    private const RIOT_TAGS_NEWPLAYER_DEMO = [
        'Yone#boo',
        'Ryssa#EUW',
        'Tony#ZED',
        'Capy#BGCE',
        'Liwa#1805',
        'Bebelin#EUW',
        'BKS#KDR',
        'Caliste#Franc',
        'Percy Magic#1234',
        'Lurox#Lurox',
    ];

    private const OPPONENT_STARTER_ROLES = ['top', 'jungle', 'mid', 'bot', 'support'];

    private const TARGET_SCRIM_RECORDS = 100;

    /**
     * Composition des 5 équipes : cinq titulaires (TOP→Support) + un pseudo staff (le coach est toujours l’utilisateur test).
     *
     * @var list<array{name: string, slug: string, tag: string, players: list<string>, staff: string}>
     */
    private const TEAM_BLUEPRINTS = [
        [
            'name' => 'Phoenix Elite',
            'slug' => 'phoenix-elite',
            'tag' => 'PHNX',
            'players' => ['Elise', 'Mizuty', 'Alucard', 'Yone', 'Ryssa'],
            'staff' => 'Tokha',
        ],
        [
            'name' => 'Dragon Lane FR',
            'slug' => 'dragon-lane-fr',
            'tag' => 'DRGN',
            'players' => ['Tony', 'Nekkore', 'eNami', 'mamou', 'Capy'],
            'staff' => 'Pollo',
        ],
        [
            'name' => 'Lynx Scrims',
            'slug' => 'lynx-scrims',
            'tag' => 'LNX',
            'players' => ['Liwa', 'sorrow', 'Mizuty', 'Lawin', 'Bebelin'],
            'staff' => 'Drogas',
        ],
        [
            'name' => 'Raven Draft',
            'slug' => 'raven-draft',
            'tag' => 'RVN',
            'players' => ['BKS', 'Tokha', 'Elise', 'eNami', 'Caliste'],
            'staff' => 'Nekkore',
        ],
        [
            'name' => 'Wolf Pack EUW',
            'slug' => 'wolf-pack-euw',
            'tag' => 'WLF',
            'players' => ['Alucard', 'Pollo', 'Lawin', 'Percy Magic', 'Lurox'],
            'staff' => 'mamou',
        ],
    ];

    private const STARTER_ROLES = [
        RoleInGame::TOP,
        RoleInGame::JUNGLE,
        RoleInGame::MID,
        RoleInGame::ADC,
        RoleInGame::SUPPORT,
    ];

    public function run(): void
    {
        $usersByUsername = [];

        $testUser = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'current_team_id' => null,
        ]);

        $usersByUsername['testuser'] = $testUser;

        $totalPlayers = count(self::RIOT_USERS) + count(self::RIOT_TAGS_NEWPLAYER_DEMO);
        $seededPlayers = 0;

        $this->logSeederProgress(sprintf(
            'Récupération des profils Riot (%d joueur(s))…',
            $totalPlayers,
        ));

        foreach (self::RIOT_USERS as $row) {
            $user = User::create([
                'username' => $row['username'],
                'email' => Str::lower($row['username']).'@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'current_team_id' => null,
                'avatar_type' => 'default',
                'avatar_value' => fake()->randomElement(DefaultAvatar::cases())->value,
            ]);

            $this->seedRiotDataForUser($user, $row['riot_tag']);
            $usersByUsername[$row['username']] = $user;

            $seededPlayers++;
            $this->logPlayerSeeded($row['username'], $seededPlayers, $totalPlayers);

            $this->delayBetweenUsers();
        }

        foreach (self::RIOT_TAGS_NEWPLAYER_DEMO as $riotTag) {
            $gameName = $this->riotGameNameFromTag($riotTag);
            $user = User::create([
                'username' => $gameName,
                'email' => $this->emailLocalPartFromGameName($gameName).'@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'current_team_id' => null,
                'avatar_type' => 'default',
                'avatar_value' => fake()->randomElement(DefaultAvatar::cases())->value,
            ]);

            $this->seedRiotDataForUser($user, $riotTag);
            $usersByUsername[$gameName] = $user;

            $seededPlayers++;
            $this->logPlayerSeeded($gameName, $seededPlayers, $totalPlayers);

            $this->delayBetweenUsers();
        }

        $this->logSeederProgress('Création des équipes, devoirs et scrims…');

        $teams = [];
        foreach (self::TEAM_BLUEPRINTS as $blueprint) {
            $team = Team::create([
                'name' => $blueprint['name'],
                'slug' => $blueprint['slug'],
                'tag' => $blueprint['tag'],
                'logo_type' => 'default',
                'logo_value' => fake()->randomElement(DefaultTeam::cases())->value,
                'description' => 'Équipe de démonstration générée par le seeder.',
                'language' => fake()->randomElement(Language::cases())->value,
                'server' => fake()->randomElement(LolServeur::cases())->value,
                'goal' => fake()->randomElement(LolGoal::cases())->value,
                'starter_average_elo' => null,
                'creator_id' => $testUser->id,
            ]);
            $teams[] = $team;

            $coachMember = $this->attachMember(
                $team,
                $testUser,
                RoleInTeam::COACH,
                null,
                false
            );

            $staffUsername = $blueprint['staff'];
            $this->attachMember(
                $team,
                $usersByUsername[$staffUsername],
                RoleInTeam::STAFF,
                null,
                false
            );

            foreach ($blueprint['players'] as $index => $playerUsername) {
                $this->attachMember(
                    $team,
                    $usersByUsername[$playerUsername],
                    RoleInTeam::PLAYER,
                    self::STARTER_ROLES[$index],
                    true
                );
            }

            $team->averageEloScore();

            $this->seedTasksForTeam($team, $coachMember);
            $this->seedPlayerAvailabilitiesForTeam($team);
        }

        $teams[] = $this->seedJuryTeam($usersByUsername, $testUser);

        $this->seedScrimsAndScrimRequests($teams);

        if (isset($teams[0])) {
            $testUser->update(['current_team_id' => $teams[0]->id]);
        }

        foreach ($usersByUsername as $username => $user) {
            if ($username === 'testuser') {
                continue;
            }
            $firstMembership = TeamMember::where('user_id', $user->id)->orderBy('id')->first();
            if ($firstMembership) {
                $user->update(['current_team_id' => $firstMembership->team_id]);
            }
        }
    }

    /**
     * Génère ~100 scrims (une seule par équipe et par jour) et quelques demandes en attente.
     *
     * @param  list<Team>  $teams
     */
    private function seedScrimsAndScrimRequests(array $teams): void
    {
        $n = count($teams);
        if ($n < 2) {
            return;
        }

        /** @var array<int, array<string, true>> $busyDatesByTeamId */
        $busyDatesByTeamId = [];

        $this->seedBulkScrimPairs($teams, $busyDatesByTeamId);

        if ($n < 4) {
            return;
        }

        $pendingPerTeam = 2;

        foreach ($this->buildPendingScrimRequestIndexPairs($n, $pendingPerTeam) as [$requesterIndex, $receiverIndex]) {
            $slot = $this->allocateScrimSlot(
                $teams[$requesterIndex]->id,
                $teams[$receiverIndex]->id,
                now()->addDays(2)->startOfDay(),
                now()->addDays(60)->endOfDay(),
                $busyDatesByTeamId,
            );

            if ($slot === null) {
                continue;
            }

            $this->reserveTeamsOnDate(
                $busyDatesByTeamId,
                $teams[$requesterIndex]->id,
                $teams[$receiverIndex]->id,
                $slot['scheduled_date'],
            );

            ScrimRequest::create([
                'status' => StatusScrimRequest::PENDING,
                'scheduled_date' => $slot['scheduled_date'],
                'scheduled_time' => $slot['scheduled_time'],
                'number_of_games' => $slot['number_of_games'],
                'message' => fake()->optional(0.5)->sentence(),
                'responded_at' => null,
                'requester_team_id' => $teams[$requesterIndex]->id,
                'receiver_team_id' => $teams[$receiverIndex]->id,
            ]);
        }
    }

    /**
     * @param  list<Team>  $teams
     * @param  array<int, array<string, true>>  $busyDatesByTeamId
     */
    private function seedBulkScrimPairs(array $teams, array &$busyDatesByTeamId): void
    {
        $targetPairs = (int) (self::TARGET_SCRIM_RECORDS / 2);
        $pairsCreated = 0;

        $teamsById = collect($teams)->keyBy('id');
        $schedulingDates = collect($this->buildScrimSchedulingDates());
        $orderedDates = $schedulingDates
            ->filter(fn (CarbonInterface $date): bool => $date->copy()->endOfDay()->lte(now()))
            ->shuffle()
            ->concat($schedulingDates->filter(fn (CarbonInterface $date): bool => $date->copy()->startOfDay()->gt(now()))->shuffle());

        foreach ($orderedDates as $date) {
            if ($pairsCreated >= $targetPairs) {
                break;
            }

            $freeTeamIds = [];

            foreach ($teams as $team) {
                if (! $this->isTeamBusyOnDate($busyDatesByTeamId, $team->id, $date)) {
                    $freeTeamIds[] = $team->id;
                }
            }

            shuffle($freeTeamIds);

            while (count($freeTeamIds) >= 2 && $pairsCreated < $targetPairs) {
                $requesterTeamId = array_pop($freeTeamIds);
                $receiverTeamId = array_pop($freeTeamIds);

                $status = $date->copy()->endOfDay()->lte(now())
                    ? StatusScrim::COMPLETED
                    : StatusScrim::SCHEDULED;

                $slot = $this->slotForDate($date);
                $this->reserveTeamsOnDate($busyDatesByTeamId, $requesterTeamId, $receiverTeamId, $slot['scheduled_date']);

                $this->createScrimPairWithSlot(
                    $teamsById[$requesterTeamId],
                    $teamsById[$receiverTeamId],
                    $slot,
                    $status,
                );

                $pairsCreated++;
            }
        }
    }

    /**
     * @return list<CarbonInterface>
     */
    private function buildScrimSchedulingDates(): array
    {
        $dates = [];

        $pastStart = now()->subMonth()->startOfMonth();
        $pastEnd = now()->subHour();

        if ($pastEnd->lt($pastStart)) {
            $pastEnd = $pastStart->copy()->addDay();
        }

        for ($date = $pastStart->copy(); $date->lte($pastEnd); $date = $date->copy()->addDay()) {
            $dates[] = $date->copy()->startOfDay();
        }

        $futureStart = now()->addDays(2)->startOfDay();
        $futureEnd = now()->addDays(60)->startOfDay();

        for ($date = $futureStart->copy(); $date->lte($futureEnd); $date = $date->copy()->addDay()) {
            $dates[] = $date->copy();
        }

        return $dates;
    }

    /**
     * @param  array<int, array<string, true>>  $busyDatesByTeamId
     * @return array{scheduled_date: CarbonInterface, scheduled_time: string, number_of_games: int}|null
     */
    private function allocateScrimSlot(
        int $teamAId,
        int $teamBId,
        CarbonInterface $rangeStart,
        CarbonInterface $rangeEnd,
        array $busyDatesByTeamId,
    ): ?array {
        $rangeStartDay = $rangeStart->copy()->startOfDay();
        $rangeEndDay = $rangeEnd->copy()->startOfDay();

        $candidates = collect($this->buildScrimSchedulingDates())
            ->filter(function (CarbonInterface $date) use ($rangeStartDay, $rangeEndDay, $busyDatesByTeamId, $teamAId, $teamBId): bool {
                $day = $date->copy()->startOfDay();

                return $day->gte($rangeStartDay)
                    && $day->lte($rangeEndDay)
                    && ! $this->isTeamBusyOnDate($busyDatesByTeamId, $teamAId, $date)
                    && ! $this->isTeamBusyOnDate($busyDatesByTeamId, $teamBId, $date);
            })
            ->shuffle();

        $date = $candidates->first();

        if ($date === null) {
            return null;
        }

        return $this->slotForDate($date);
    }

    /**
     * @return array{scheduled_date: CarbonInterface, scheduled_time: string, number_of_games: int}
     */
    private function slotForDate(CarbonInterface $date): array
    {
        return [
            'scheduled_date' => $date->copy()->startOfDay(),
            'scheduled_time' => sprintf('%02d:%02d:00', fake()->numberBetween(18, 22), fake()->randomElement([0, 15, 30, 45])),
            'number_of_games' => fake()->numberBetween(2, 5),
        ];
    }

    /**
     * @param  array<int, array<string, true>>  $busyDatesByTeamId
     */
    private function reserveTeamsOnDate(array &$busyDatesByTeamId, int $teamAId, int $teamBId, CarbonInterface $date): void
    {
        $dateKey = $date->toDateString();
        $busyDatesByTeamId[$teamAId][$dateKey] = true;
        $busyDatesByTeamId[$teamBId][$dateKey] = true;
    }

    /**
     * @param  array<int, array<string, true>>  $busyDatesByTeamId
     */
    private function isTeamBusyOnDate(array $busyDatesByTeamId, int $teamId, CarbonInterface $date): bool
    {
        return isset($busyDatesByTeamId[$teamId][$date->toDateString()]);
    }

    private function createScrimPairWithSlot(
        Team $requesterTeam,
        Team $receiverTeam,
        array $slot,
        StatusScrim $status,
    ): void {
        $scheduledAt = Carbon::parse(
            $slot['scheduled_date']->format('Y-m-d').' '.$slot['scheduled_time']
        );
        $withSummary = $status === StatusScrim::COMPLETED && fake()->boolean(60);

        $request = ScrimRequest::create([
            'status' => StatusScrimRequest::ACCEPTED,
            'scheduled_date' => $slot['scheduled_date'],
            'scheduled_time' => $slot['scheduled_time'],
            'number_of_games' => $slot['number_of_games'],
            'message' => fake()->optional(0.35)->sentence(),
            'responded_at' => $status === StatusScrim::COMPLETED
                ? $scheduledAt->copy()->subDays(fake()->numberBetween(1, 5))
                : now(),
            'requester_team_id' => $requesterTeam->id,
            'receiver_team_id' => $receiverTeam->id,
        ]);

        [$scrimReceiver, $scrimRequester] = $this->createAcceptedScrimPair(
            $request,
            $status,
            $withSummary ? fake()->paragraph() : null,
            $withSummary ? fake()->paragraph() : null,
            $withSummary ? fake()->paragraph() : null,
        );

        if ($status === StatusScrim::COMPLETED) {
            $this->seedMirroredGames($scrimRequester, $scrimReceiver);
        }
    }

    /**
     * Construit des paires (index demandeur, index destinataire) pour les demandes en attente.
     * Aucune paire d’équipes ne possède une demande dans les deux sens.
     *
     * @return list<array{0: int, 1: int}>
     */
    private function buildPendingScrimRequestIndexPairs(int $teamCount, int $pendingPerTeam): array
    {
        /** @var array<string, true> $directedPairs */
        $directedPairs = [];

        for ($i = 0; $i < $teamCount; $i++) {
            $directedPairs["{$i}:".(($i + 1) % $teamCount)] = true;
        }

        $outDegree = array_fill(0, $teamCount, 0);
        $inDegree = array_fill(0, $teamCount, 0);

        /** @var list<array{0: int, 1: int}> $pairs */
        $pairs = [];

        if ($teamCount % 2 === 1) {
            for ($requesterIndex = 0; $requesterIndex < $teamCount; $requesterIndex++) {
                $receiverIndex = ($requesterIndex + 2) % $teamCount;
                $this->tryAddPendingScrimRequestPair(
                    $requesterIndex,
                    $receiverIndex,
                    $directedPairs,
                    $outDegree,
                    $inDegree,
                    $pairs,
                    $pendingPerTeam,
                    allowDuplicateForward: false,
                );
            }
        }

        for ($requesterIndex = 0; $requesterIndex < $teamCount; $requesterIndex++) {
            while ($outDegree[$requesterIndex] < $pendingPerTeam) {
                $receiverIndex = ($requesterIndex + 1) % $teamCount;
                $this->tryAddPendingScrimRequestPair(
                    $requesterIndex,
                    $receiverIndex,
                    $directedPairs,
                    $outDegree,
                    $inDegree,
                    $pairs,
                    $pendingPerTeam,
                    allowDuplicateForward: true,
                );
            }
        }

        return $pairs;
    }

    /**
     * @param  array<string, true>  $directedPairs
     * @param  list<int>  $outDegree
     * @param  list<int>  $inDegree
     * @param  list<array{0: int, 1: int}>  $pairs
     */
    private function tryAddPendingScrimRequestPair(
        int $requesterIndex,
        int $receiverIndex,
        array &$directedPairs,
        array &$outDegree,
        array &$inDegree,
        array &$pairs,
        int $pendingPerTeam,
        bool $allowDuplicateForward,
    ): void {
        if ($outDegree[$requesterIndex] >= $pendingPerTeam) {
            return;
        }

        if ($inDegree[$receiverIndex] >= $pendingPerTeam) {
            return;
        }

        $forwardKey = "{$requesterIndex}:{$receiverIndex}";
        $reverseKey = "{$receiverIndex}:{$requesterIndex}";

        if (isset($directedPairs[$reverseKey])) {
            return;
        }

        if (! $allowDuplicateForward && isset($directedPairs[$forwardKey])) {
            return;
        }

        $directedPairs[$forwardKey] = true;
        $pairs[] = [$requesterIndex, $receiverIndex];
        $outDegree[$requesterIndex]++;
        $inDegree[$receiverIndex]++;
    }

    /**
     * Reproduit la création des scrims après acceptation (modale show-scrim-request).
     *
     * @return array{0: Scrim, 1: Scrim}
     */
    private function createAcceptedScrimPair(
        ScrimRequest $request,
        StatusScrim $status = StatusScrim::SCHEDULED,
        ?string $summary = null,
        ?string $advantages = null,
        ?string $disadvantages = null,
    ): array {
        $scrimReceiver = Scrim::create([
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'number_of_games' => $request->number_of_games,
            'status' => $status,
            'summary' => $summary,
            'advantages' => $advantages,
            'disadvantages' => $disadvantages,
            'scrim_request_id' => $request->id,
            'opponent_team_id' => $request->requester_team_id,
            'team_id' => $request->receiver_team_id,
        ]);

        $scrimRequester = Scrim::create([
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'number_of_games' => $request->number_of_games,
            'status' => $status,
            'summary' => $summary,
            'advantages' => $advantages,
            'disadvantages' => $disadvantages,
            'scrim_request_id' => $request->id,
            'opponent_team_id' => $request->receiver_team_id,
            'team_id' => $request->requester_team_id,
        ]);

        return [$scrimReceiver, $scrimRequester];
    }

    private function seedMirroredGames(Scrim $scrimRequester, Scrim $scrimReceiver): void
    {
        $payloads = $this->buildScrimGamePayloads($scrimRequester);

        $this->persistScrimGamePayloads($scrimRequester, $payloads);
        $this->persistScrimGamePayloads($scrimReceiver, $this->mirrorScrimGamePayloads(
            $scrimRequester,
            $scrimReceiver,
            $payloads,
        ));

        $this->syncScrimOutcomeFromGames($scrimRequester);
        $this->syncScrimOutcomeFromGames($scrimReceiver);
    }

    private function syncScrimOutcomeFromGames(Scrim $scrim): void
    {
        $wins = $scrim->scrimGames()->where('is_victory', true)->count();
        $losses = $scrim->scrimGames()->where('is_victory', false)->count();

        $scrim->update([
            'outcome' => ScrimOutcome::fromCounts($wins, $losses),
        ]);
    }

    /**
     * @return list<array{
     *     title: string,
     *     duration: int,
     *     is_victory: bool,
     *     notes: ?string,
     *     home_players: array<int, array{champion: string, kills: int, deaths: int, assists: int, cs: int}>,
     *     opponent_starters: array<string, array{champion: string, kills: int, deaths: int, assists: int}>,
     *     scrim_game_notes: list<array{type: TypeScrimGameNote, note: string}>
     * }>
     */
    private function buildScrimGamePayloads(Scrim $scrim): array
    {
        $starters = $this->starterMembersForTeam($scrim->team_id);
        $championPool = $this->championNames();
        $payloads = [];

        for ($gameIndex = 1; $gameIndex <= $scrim->number_of_games; $gameIndex++) {
            $usedChampions = [];
            $homePlayers = [];
            $opponentStarters = [];

            foreach ($starters as $member) {
                $champion = $this->pickUniqueChampion($championPool, $usedChampions);
                $homePlayers[$member->id] = [
                    'champion' => $champion,
                    'kills' => fake()->numberBetween(0, 12),
                    'deaths' => fake()->numberBetween(0, 8),
                    'assists' => fake()->numberBetween(0, 18),
                    'cs' => fake()->numberBetween(120, 280),
                ];
            }

            foreach (self::OPPONENT_STARTER_ROLES as $role) {
                $champion = $this->pickUniqueChampion($championPool, $usedChampions);
                $opponentStarters[$role] = [
                    'champion' => $champion,
                    'kills' => fake()->numberBetween(0, 12),
                    'deaths' => fake()->numberBetween(0, 8),
                    'assists' => fake()->numberBetween(0, 18),
                ];
            }

            $gameNotes = [];
            if (fake()->boolean(30)) {
                $gameNotes[] = [
                    'type' => TypeScrimGameNote::POSITIVE,
                    'note' => fake()->sentence(),
                ];
            }
            if (fake()->boolean(30)) {
                $gameNotes[] = [
                    'type' => TypeScrimGameNote::NEGATIVE,
                    'note' => fake()->sentence(),
                ];
            }

            $payloads[] = [
                'title' => 'Game '.$gameIndex,
                'duration' => fake()->numberBetween(1200, 2400),
                'is_victory' => fake()->boolean(55),
                'notes' => fake()->optional(0.25)->sentence(),
                'home_players' => $homePlayers,
                'opponent_starters' => $opponentStarters,
                'scrim_game_notes' => $gameNotes,
            ];
        }

        return $payloads;
    }

    /**
     * @param  list<array{
     *     title: string,
     *     duration: int,
     *     is_victory: bool,
     *     notes: ?string,
     *     home_players: array<int, array{champion: string, kills: int, deaths: int, assists: int, cs: int}>,
     *     opponent_starters: array<string, array{champion: string, kills: int, deaths: int, assists: int}>,
     *     scrim_game_notes: list<array{type: TypeScrimGameNote, note: string}>
     * }>  $payloads
     * @return list<array{
     *     title: string,
     *     duration: int,
     *     is_victory: bool,
     *     notes: ?string,
     *     home_players: array<int, array{champion: string, kills: int, deaths: int, assists: int, cs: int}>,
     *     opponent_starters: array<string, array{champion: string, kills: int, deaths: int, assists: int}>,
     *     scrim_game_notes: list<array{type: TypeScrimGameNote, note: string}>
     * }>
     */
    private function mirrorScrimGamePayloads(Scrim $sourceScrim, Scrim $targetScrim, array $payloads): array
    {
        $sourceStarters = $this->starterMembersForTeam($sourceScrim->team_id);
        $targetStarters = $this->starterMembersForTeam($targetScrim->team_id);

        $mirrored = [];

        foreach ($payloads as $payload) {
            $homePlayers = [];
            $opponentStarters = [];

            foreach ($targetStarters as $member) {
                $roleKey = $this->roleInGameToOpponentKey($member->roleInGame);
                $opponentStats = $payload['opponent_starters'][$roleKey];
                $homePlayers[$member->id] = [
                    'champion' => $opponentStats['champion'],
                    'kills' => $opponentStats['kills'],
                    'deaths' => $opponentStats['deaths'],
                    'assists' => $opponentStats['assists'],
                    'cs' => fake()->numberBetween(120, 280),
                ];
            }

            foreach ($sourceStarters as $member) {
                $roleKey = $this->roleInGameToOpponentKey($member->roleInGame);
                $homeStats = $payload['home_players'][$member->id];
                $opponentStarters[$roleKey] = [
                    'champion' => $homeStats['champion'],
                    'kills' => $homeStats['kills'],
                    'deaths' => $homeStats['deaths'],
                    'assists' => $homeStats['assists'],
                ];
            }

            $mirrored[] = [
                'title' => $payload['title'],
                'duration' => $payload['duration'],
                'is_victory' => ! $payload['is_victory'],
                'notes' => $payload['notes'],
                'home_players' => $homePlayers,
                'opponent_starters' => $opponentStarters,
                'scrim_game_notes' => $payload['scrim_game_notes'],
            ];
        }

        return $mirrored;
    }

    /**
     * @param  list<array{
     *     title: string,
     *     duration: int,
     *     is_victory: bool,
     *     notes: ?string,
     *     home_players: array<int, array{champion: string, kills: int, deaths: int, assists: int, cs: int}>,
     *     opponent_starters: array<string, array{champion: string, kills: int, deaths: int, assists: int}>,
     *     scrim_game_notes: list<array{type: TypeScrimGameNote, note: string}>
     * }>  $payloads
     */
    private function persistScrimGamePayloads(Scrim $scrim, array $payloads): void
    {
        foreach ($payloads as $payload) {
            $scrimGame = ScrimGame::create([
                'title' => $payload['title'],
                'duration' => $payload['duration'],
                'is_victory' => $payload['is_victory'],
                'notes' => $payload['notes'],
                'scrim_id' => $scrim->id,
                'opponent_team_members_starters' => $payload['opponent_starters'],
            ]);

            foreach ($payload['home_players'] as $teamMemberId => $player) {
                ScrimGamePlayer::create([
                    'scrim_game_id' => $scrimGame->id,
                    'team_member_id' => $teamMemberId,
                    'champion' => $player['champion'],
                    'kills' => $player['kills'],
                    'deaths' => $player['deaths'],
                    'assists' => $player['assists'],
                    'cs' => $player['cs'],
                ]);
            }

            foreach ($payload['scrim_game_notes'] as $note) {
                ScrimGameNote::create([
                    'scrim_game_id' => $scrimGame->id,
                    'type' => $note['type'],
                    'note' => $note['note'],
                ]);
            }
        }
    }

    /**
     * @return Collection<int, TeamMember>
     */
    private function starterMembersForTeam(int $teamId): Collection
    {
        return TeamMember::query()
            ->where('team_id', $teamId)
            ->where('is_starter', true)
            ->orderBy('roleInGame')
            ->get();
    }

    private function roleInGameToOpponentKey(RoleInGame $role): string
    {
        return match ($role) {
            RoleInGame::TOP => 'top',
            RoleInGame::JUNGLE => 'jungle',
            RoleInGame::MID => 'mid',
            RoleInGame::ADC => 'bot',
            RoleInGame::SUPPORT => 'support',
        };
    }

    /**
     * @param  list<string>  $pool
     * @param  list<string>  $used
     */
    private function pickUniqueChampion(array $pool, array &$used): string
    {
        $available = array_values(array_diff($pool, $used));
        $champion = fake()->randomElement($available);
        $used[] = $champion;

        return $champion;
    }

    /**
     * @return list<string>
     */
    private function championNames(): array
    {
        return collect(getChampionsList())->pluck('name')->all();
    }

    private function delayBetweenUsers(): void
    {
        if (! app()->runningUnitTests()) {
            sleep(3);
        }
    }

    private function logSeederProgress(string $message): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->command?->info($message);
    }

    private function logPlayerSeeded(string $username, int $current, int $total): void
    {
        $remaining = $total - $current;

        $this->logSeederProgress(sprintf(
            'Joueur seedé : %s (%d/%d, %d restant(s))',
            $username,
            $current,
            $total,
            $remaining,
        ));
    }

    private function riotGameNameFromTag(string $riotTag): string
    {
        return trim(Str::before($riotTag, '#'));
    }

    private function emailLocalPartFromGameName(string $gameName): string
    {
        return Str::lower(str_replace(' ', '', trim($gameName)));
    }

    private function seedRiotDataForUser(User $user, string $riotTag): void
    {
        if ($this->shouldUseRiotApi()) {
            if ($this->trySeedRiotDataFromApi($user, $riotTag)) {
                return;
            }

            Log::warning('DemoDataSeeder: échec de récupération Riot API pour ce compte, données factices utilisées.', [
                'riot_tag' => $riotTag,
            ]);
        }

        $this->seedRiotDataFake($user, $riotTag);
    }

    /**
     * Utilise l’API Riot en local / staging lorsque RIOT_API_KEY est définie.
     * Désactivé pendant les tests pour éviter les appels réseau et les quotas.
     */
    private function shouldUseRiotApi(): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        return filled(config('riot.api_key'));
    }

    private function trySeedRiotDataFromApi(User $user, string $riotTag): bool
    {
        if (! str_contains($riotTag, '#')) {
            return false;
        }

        [$gameName, $tagLine] = explode('#', $riotTag, 2);
        $client = new RiotApiClient;
        $account = $client->getAccount($gameName, $tagLine);

        if ($account === null) {
            return false;
        }

        $solo = $account['soloQueue'] ?? null;
        $tier = isset($solo['tier']) ? LolTier::tryFrom((string) $solo['tier']) : null;

        $profile = RiotProfile::create([
            'user_id' => $user->id,
            'riot_tag' => $riotTag,
            'riot_puuid' => $account['puuid'],
            'tier' => $tier,
            'rank' => $solo['rank'] ?? null,
            'lp' => $solo['leaguePoints'] ?? null,
            'wins' => $solo['wins'] ?? 0,
            'losses' => $solo['losses'] ?? 0,
            'synced_at' => now(),
        ]);

        foreach ($client->getRecentMatches($account['puuid']) as $matchRow) {
            $profile->riotMatches()->create($matchRow);
        }

        return true;
    }

    private function seedRiotDataFake(User $user, string $riotTag): void
    {
        $tier = fake()->randomElement(LolTier::cases());
        $isApex = $tier->isApex();

        $profile = RiotProfile::create([
            'user_id' => $user->id,
            'riot_tag' => $riotTag,
            'riot_puuid' => Str::replace('-', '', (string) Str::uuid()),
            'tier' => $tier,
            'rank' => $isApex ? null : fake()->randomElement(['I', 'II', 'III', 'IV']),
            'lp' => $isApex ? null : fake()->numberBetween(0, 99),
            'wins' => 0,
            'losses' => 0,
            'synced_at' => now(),
        ]);

        $wins = 0;
        $losses = 0;
        $matchCount = fake()->numberBetween(6, 12);

        for ($i = 0; $i < $matchCount; $i++) {
            $win = fake()->boolean(52);
            if ($win) {
                $wins++;
            } else {
                $losses++;
            }

            RiotMatch::create([
                'riot_profile_id' => $profile->id,
                'match_id' => 'EUW1_'.Str::upper(Str::random(10)).'_'.$i,
                'game_duration' => fake()->numberBetween(1200, 2400),
                'played_at' => fake()->dateTimeBetween('-45 days', 'now'),
                'champion_name' => fake()->randomElement($this->championNames()),
                'champion_id' => fake()->numberBetween(1, 999),
                'champion_level' => fake()->numberBetween(1, 18),
                'role' => fake()->randomElement(['TOP', 'JUNGLE', 'MIDDLE', 'BOTTOM', 'UTILITY']),
                'win' => $win,
                'kills' => fake()->numberBetween(0, 15),
                'deaths' => fake()->numberBetween(0, 12),
                'assists' => fake()->numberBetween(0, 20),
                'cs' => fake()->numberBetween(120, 320),
                'items' => array_map(fn () => fake()->numberBetween(0, 6699), range(0, 6)),
            ]);
        }

        $profile->update([
            'wins' => $wins,
            'losses' => $losses,
        ]);
    }

    private function attachMember(
        Team $team,
        User $user,
        RoleInTeam $role,
        ?RoleInGame $roleInGame,
        bool $isStarter,
    ): TeamMember {
        return TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'roleInTeam' => $role,
            'roleInGame' => $roleInGame,
            'is_starter' => $isStarter,
            'status' => StatusInTeam::ACCEPTED,
            'message' => null,
            'joined_at' => now(),
        ]);
    }

    /**
     * @param  array<string, User>  $usersByUsername
     */
    private function seedJuryTeam(array $usersByUsername, User $testUser): Team
    {
        $this->logSeederProgress('Création de l\'Equipe du Jury…');

        $team = Team::create([
            'name' => 'Equipe du Jury',
            'slug' => 'equipe-du-jury',
            'tag' => 'JURY',
            'logo_type' => 'default',
            'logo_value' => fake()->randomElement(DefaultTeam::cases())->value,
            'description' => 'Équipe regroupant l\'ensemble des joueurs de démonstration.',
            'language' => fake()->randomElement(Language::cases())->value,
            'server' => fake()->randomElement(LolServeur::cases())->value,
            'goal' => fake()->randomElement(LolGoal::cases())->value,
            'starter_average_elo' => null,
            'creator_id' => $testUser->id,
        ]);

        $coachMember = $this->attachMember(
            $team,
            $testUser,
            RoleInTeam::COACH,
            null,
            false
        );

        $playerUsernames = collect($usersByUsername)
            ->keys()
            ->reject(fn (string $username): bool => $username === 'testuser')
            ->values()
            ->all();

        foreach ($playerUsernames as $index => $username) {
            $isStarter = $index < count(self::STARTER_ROLES);

            $this->attachMember(
                $team,
                $usersByUsername[$username],
                RoleInTeam::PLAYER,
                $isStarter ? self::STARTER_ROLES[$index] : fake()->randomElement(RoleInGame::cases()),
                $isStarter,
            );
        }

        $team->averageEloScore();

        $this->seedTasksForTeam($team, $coachMember);
        $this->seedPlayerAvailabilitiesForTeam($team);

        return $team;
    }

    private function seedPlayerAvailabilitiesForTeam(Team $team): void
    {
        $players = TeamMember::query()
            ->where('team_id', $team->id)
            ->where('roleInTeam', RoleInTeam::PLAYER)
            ->get();

        foreach ($players as $player) {
            $this->seedDefaultSchedulesForMember($player);
            $this->seedAbsencesForMember($player);
        }
    }

    private function seedDefaultSchedulesForMember(TeamMember $member): void
    {
        $activeDays = collect(DayOfTheWeek::cases())
            ->shuffle()
            ->take(fake()->numberBetween(3, 6));

        foreach ($activeDays as $day) {
            $slot = $this->randomAvailabilitySlot();

            PlayerDefaultSchedule::create([
                'team_member_id' => $member->id,
                'day_of_week' => $day->value,
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
            ]);
        }
    }

    private function seedAbsencesForMember(TeamMember $member): void
    {
        if (! fake()->boolean(40)) {
            return;
        }

        $absenceCount = fake()->numberBetween(1, 4);
        $usedDates = [];

        foreach (collect($this->buildScrimSchedulingDates())->shuffle() as $date) {
            if (count($usedDates) >= $absenceCount) {
                break;
            }

            $dateKey = $date->toDateString();

            if (isset($usedDates[$dateKey])) {
                continue;
            }

            $usedDates[$dateKey] = true;

            Absence::create([
                'team_member_id' => $member->id,
                'date' => $dateKey,
                'justification' => fake()->randomElement(AbsenceJustification::cases()),
            ]);
        }
    }

    /**
     * Créneau sur grille 30 min (08:00–23:00), aligné avec les règles de la modale disponibilités.
     *
     * @return array{start: string, end: string}
     */
    private function randomAvailabilitySlot(): array
    {
        if (fake()->boolean(70)) {
            $eveningStarts = ['18:00', '18:30', '19:00', '19:30', '20:00', '20:30'];
            $eveningEnds = ['21:00', '21:30', '22:00', '22:30', '23:00'];
            $start = fake()->randomElement($eveningStarts);
            $end = fake()->randomElement($eveningEnds);

            if ($this->availabilityStartIsBeforeEnd($start, $end)) {
                return ['start' => $start, 'end' => $end];
            }
        }

        $startSlot = fake()->numberBetween(0, 22);
        $length = fake()->numberBetween(4, 8);
        $endSlot = min($startSlot + $length, 30);

        return [
            'start' => $this->formatAvailabilityHalfHour($startSlot),
            'end' => $this->formatAvailabilityHalfHour($endSlot),
        ];
    }

    private function formatAvailabilityHalfHour(int $slotIndex): string
    {
        $totalMinutes = (8 * 60) + ($slotIndex * 30);

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    }

    private function availabilityStartIsBeforeEnd(string $start, string $end): bool
    {
        [$startHour, $startMinute] = array_map(intval(...), explode(':', $start));
        [$endHour, $endMinute] = array_map(intval(...), explode(':', $end));

        return ($startHour * 60 + $startMinute) < ($endHour * 60 + $endMinute);
    }

    private function seedTasksForTeam(Team $team, TeamMember $coachMember): void
    {
        $members = TeamMember::where('team_id', $team->id)->get();
        $templates = $this->devoirTemplates();

        foreach ($members as $assignee) {
            $devoirs = collect($templates)->shuffle()->take(self::TASKS_PER_MEMBER);

            foreach ($devoirs as $template) {
                $status = fake()->randomElement(StatusTask::cases());
                $subtasks = $template['subtasks'];
                $subtaskCount = count($subtasks);

                $completedPattern = match ($status) {
                    StatusTask::TODO => array_fill(0, $subtaskCount, false),
                    StatusTask::DONE => array_fill(0, $subtaskCount, true),
                    StatusTask::IN_PROGRESS => $subtaskCount > 1
                        ? $this->randomPartialCompletion($subtaskCount)
                        : [false],
                };

                $task = Task::create([
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'status' => $status,
                    'deadline' => fake()->boolean(70)
                        ? fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d')
                        : null,
                    'created_by' => $coachMember->id,
                    'team_member_id' => $assignee->id,
                    'team_id' => $team->id,
                    'completed_at' => $status === StatusTask::DONE ? now() : null,
                ]);

                foreach ($subtasks as $index => $subtaskTitle) {
                    Subtask::create([
                        'task_id' => $task->id,
                        'title' => $subtaskTitle,
                        'is_completed' => $completedPattern[$index],
                    ]);
                }
            }
        }
    }

    /**
     * @return list<array{title: string, description: string, subtasks: list<string>}>
     */
    private function devoirTemplates(): array
    {
        if ($this->devoirTemplates !== null) {
            return $this->devoirTemplates;
        }

        $path = database_path('seeders/data/devoirs-seeder.json');

        if (! File::exists($path)) {
            throw new RuntimeException("Catalogue de devoirs introuvable : {$path}");
        }

        /** @var list<array{title: string, description: string, subtasks: list<string>}> $templates */
        $templates = File::json($path);

        $this->devoirTemplates = $templates;

        return $this->devoirTemplates;
    }

    /**
     * @return list<bool>
     */
    private function randomPartialCompletion(int $subtaskCount): array
    {
        $completed = fake()->numberBetween(1, $subtaskCount - 1);
        $pattern = array_fill(0, $subtaskCount, false);
        $indices = collect(range(0, $subtaskCount - 1))->shuffle()->take($completed)->all();
        foreach ($indices as $i) {
            $pattern[$i] = true;
        }

        return $pattern;
    }
}
