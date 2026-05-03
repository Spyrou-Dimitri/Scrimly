<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\StatusInTeam;

class EnsureUserHasActiveTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('slug');
        $user = $request->user();

        if (! $user->current_team_id) {
            return redirect()->route('team.index')->with('error', 'You must have an active team to access this page.');
        }

        $team = $user->currentTeam;

        if (! $team) {
            $user->update(['current_team_id' => null]);

            return redirect()->route('team.index')->with('error', 'Vous ne faites pas partie de cette équipe.');
        }

        $isAccepted = $user->teams()->where('team_id', $team->id)->where('status', StatusInTeam::ACCEPTED)->exists();

        if (! $isAccepted) {
            return redirect()->route('team.index')->with('error', 'Vous ne faites pas partie de cette équipe.');
        }

        if ($slug !== $team->slug) {
            abort(404);
        }

        return $next($request);
    }
}
