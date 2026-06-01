<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\Scrim;

class WelcomeController extends Controller
{
    public function index()
    {
        $numbersTeams = Team::count();
        $numbersUsers = User::count();
        $numbersScrims = Scrim::count();
        $realNumbersScrims = $numbersScrims / 2;

        return view('welcome', compact('numbersTeams', 'numbersUsers', 'numbersScrims', 'realNumbersScrims'));
    }
}
