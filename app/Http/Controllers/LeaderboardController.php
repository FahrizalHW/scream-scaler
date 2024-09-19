<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function checkName(Request $request)
{
    $name = $request->input('name');

    $exists = Leaderboard::where('name', $name)->exists();

    return response()->json(['exists' => $exists]);
}

    public function index()
    {
       // Ambil data dari tabel leaderboard, urutkan berdasarkan scream_scale tertinggi dan batasi hingga 10
       $leaderboards = Leaderboard::orderBy('scream_scale', 'desc')->take(10)->get();

       // Kirim data ke view
       return view('index', compact('leaderboards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'scream_scale' => 'required|integer',
        ]);

        $screamScale = $request->scream_scale;

        // Ambil 10 skor teratas
        $topScores = Leaderboard::orderBy('scream_scale', 'desc')->take(10)->pluck('scream_scale');

        // Cek apakah skor pengguna termasuk dalam 10 besar
        if ($topScores->count() < 10 || $screamScale > $topScores->last()) {
            Leaderboard::create([
                'name' => $request->name,
                'scream_scale' => $screamScale,
            ]);

            // Ambil data leaderboard setelah penambahan
            $leaderboards = Leaderboard::orderBy('scream_scale', 'desc')->take(10)->get();

            return response()->json($leaderboards);
        } else {
            return response()->json(['error' => 'Your scream score did not make it to the leaderboard.'], 400);
        }
    }   
}
