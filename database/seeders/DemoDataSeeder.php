<?php

namespace Database\Seeders;

use App\Enums\DefaultAvatar;
use App\Enums\DefaultTeam;
use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\LolTier;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Enums\StatusScrim;
use App\Enums\StatusScrimRequest;
use App\Enums\StatusTask;
use App\Models\RiotMatch;
use App\Models\RiotProfile;
use App\Models\Scrim;
use App\Models\ScrimRequest;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Riot\RiotApiClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
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

    private const CHAMPION_NAMES = [
        'Ahri', 'Yasuo', 'LeeSin', 'Jinx', 'Thresh', 'Ornn', 'Kaisa', 'Graves',
        'Lulu', 'Syndra', 'Vi', 'Maokai', 'Aphelios', 'Renata', 'JarvanIV',
    ];

    /**
     * Composition des 5 équipes : trois pseudos joueurs + un pseudo staff (le coach est toujours l’utilisateur test).
     *
     * @var list<array{name: string, slug: string, tag: string, players: list<string>, staff: string}>
     */
    private const TEAM_BLUEPRINTS = [
        [
            'name' => 'Phoenix Elite',
            'slug' => 'phoenix-elite',
            'tag' => 'PHNX',
            'players' => ['Elise', 'Mizuty', 'Alucard'],
            'staff' => 'Tokha',
        ],
        [
            'name' => 'Dragon Lane FR',
            'slug' => 'dragon-lane-fr',
            'tag' => 'DRGN',
            'players' => ['Nekkore', 'eNami', 'mamou'],
            'staff' => 'Pollo',
        ],
        [
            'name' => 'Lynx Scrims',
            'slug' => 'lynx-scrims',
            'tag' => 'LNX',
            'players' => ['sorrow', 'Lawin', 'Mizuty'],
            'staff' => 'Drogas',
        ],
        [
            'name' => 'Raven Draft',
            'slug' => 'raven-draft',
            'tag' => 'RVN',
            'players' => ['Tokha', 'Elise', 'eNami'],
            'staff' => 'Nekkore',
        ],
        [
            'name' => 'Wolf Pack EUW',
            'slug' => 'wolf-pack-euw',
            'tag' => 'WLF',
            'players' => ['Alucard', 'Pollo', 'Lawin'],
            'staff' => 'mamou',
        ],
    ];

    private const STARTER_ROLES = [RoleInGame::TOP, RoleInGame::JUNGLE, RoleInGame::MID];

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

            $this->delayBetweenUsers();
        }

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
        }

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
     * Génère un graphe régulier de demandes en attente (3 entrantes / 3 sortantes par équipe)
     * et un cycle de demandes acceptées avec scrims « upcoming », tant qu’il y a au moins 4 équipes.
     *
     * @param  list<Team>  $teams
     */
    private function seedScrimsAndScrimRequests(array $teams): void
    {
        $n = count($teams);
        if ($n < 4) {
            return;
        }

        for ($i = 0; $i < $n; $i++) {
            for ($k = 1; $k <= 3; $k++) {
                $slot = $this->randomFutureScrimSlot();
                ScrimRequest::create([
                    'status' => StatusScrimRequest::PENDING,
                    'scheduled_date' => $slot['scheduled_date'],
                    'scheduled_time' => $slot['scheduled_time'],
                    'number_of_games' => $slot['number_of_games'],
                    'message' => fake()->optional(0.5)->sentence(),
                    'responded_at' => null,
                    'requester_team_id' => $teams[($i + $k) % $n]->id,
                    'receiver_team_id' => $teams[$i]->id,
                ]);
            }
        }

        for ($i = 0; $i < $n; $i++) {
            $slot = $this->randomFutureScrimSlot();
            $request = ScrimRequest::create([
                'status' => StatusScrimRequest::ACCEPTED,
                'scheduled_date' => $slot['scheduled_date'],
                'scheduled_time' => $slot['scheduled_time'],
                'number_of_games' => $slot['number_of_games'],
                'message' => null,
                'responded_at' => now(),
                'requester_team_id' => $teams[$i]->id,
                'receiver_team_id' => $teams[($i + 1) % $n]->id,
            ]);
            $this->createAcceptedScrimPair($request);
        }
    }

    /**
     * Reproduit la création des scrims après acceptation (modale show-scrim-request).
     */
    private function createAcceptedScrimPair(ScrimRequest $request): void
    {
        Scrim::create([
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'number_of_games' => $request->number_of_games,
            'status' => StatusScrim::SCHEDULED,
            'notes' => null,
            'advantages' => null,
            'disadvantages' => null,
            'scrim_request_id' => $request->id,
            'opponent_team_id' => $request->requester_team_id,
            'team_id' => $request->receiver_team_id,
        ]);
        Scrim::create([
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'number_of_games' => $request->number_of_games,
            'status' => StatusScrim::SCHEDULED,
            'notes' => null,
            'advantages' => null,
            'disadvantages' => null,
            'scrim_request_id' => $request->id,
            'opponent_team_id' => $request->receiver_team_id,
            'team_id' => $request->requester_team_id,
        ]);
    }

    /**
     * @return array{scheduled_date: Carbon, scheduled_time: string, number_of_games: int}
     */
    private function randomFutureScrimSlot(): array
    {
        $date = now()->addDays(fake()->numberBetween(2, 60))->startOfDay();

        return [
            'scheduled_date' => $date,
            'scheduled_time' => sprintf('%02d:%02d:00', fake()->numberBetween(18, 22), fake()->randomElement([0, 15, 30, 45])),
            'number_of_games' => fake()->numberBetween(2, 5),
        ];
    }

    private function delayBetweenUsers(): void
    {
        if (! app()->runningUnitTests()) {
            sleep(3);
        }
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
                'champion_name' => fake()->randomElement(self::CHAMPION_NAMES),
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

    private function seedTasksForTeam(Team $team, TeamMember $coachMember): void
    {
        $members = TeamMember::where('team_id', $team->id)->get();

        foreach ($members as $assignee) {
            for ($t = 0; $t < 4; $t++) {
                $status = fake()->randomElement(StatusTask::cases());

                $subtaskCount = match ($status) {
                    StatusTask::TODO => fake()->numberBetween(1, 4),
                    StatusTask::IN_PROGRESS => fake()->numberBetween(2, 5),
                    StatusTask::DONE => fake()->numberBetween(1, 4),
                };

                $completedPattern = match ($status) {
                    StatusTask::TODO => array_fill(0, $subtaskCount, false),
                    StatusTask::DONE => array_fill(0, $subtaskCount, true),
                    StatusTask::IN_PROGRESS => $this->randomPartialCompletion($subtaskCount),
                };

                $task = Task::create([
                    'title' => 'Devoir : '.fake()->words(3, true),
                    'description' => fake()->optional(0.6)->sentence(),
                    'status' => $status,
                    'deadline' => fake()->boolean(70)
                        ? fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d')
                        : null,
                    'created_by' => $coachMember->id,
                    'team_member_id' => $assignee->id,
                    'team_id' => $team->id,
                    'completed_at' => $status === StatusTask::DONE ? now() : null,
                ]);

                foreach ($completedPattern as $done) {
                    Subtask::create([
                        'task_id' => $task->id,
                        'title' => 'Étape : '.fake()->words(2, true),
                        'is_completed' => $done,
                    ]);
                }
            }
        }
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
