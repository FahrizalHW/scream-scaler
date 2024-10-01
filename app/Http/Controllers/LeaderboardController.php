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

    /**
     * Menampilkan daftar leaderboard.
     */
    public function index()
    {
       // Ambil 10 skor tertinggi dari leaderboard
       $leaderboards = Leaderboard::orderBy('scream_scale', 'desc')->take(10)->get();
       
       // Kirim data leaderboard ke view
       return view('index', compact('leaderboards'));
    }

    /**
     * Menyimpan skor scream baru.
     */
    public function store(Request $request)
    {
        // Validasi request
        $request->validate([
            'name' => 'required|string',
            'scream_scale' => 'required|integer|min:100',  // Pastikan skor minimal 100
        ]);

        // Ambil skor scream dari request
        $screamScale = $request->scream_scale;
        $name = $request->name;

        // Cek jika sudah ada entri dengan nama yang sama, maka update skor
        $existingEntry = Leaderboard::where('name', $name)->first();

        if ($existingEntry) {
            // Jika skor baru lebih besar, update entry
            if ($screamScale > $existingEntry->scream_scale) {
                $existingEntry->update([
                    'scream_scale' => $screamScale,
                ]);
            }
        } else {
            // Ambil 10 skor tertinggi
            $topScores = Leaderboard::orderBy('scream_scale', 'desc')->take(10)->pluck('scream_scale');

            // Cek apakah skor pengguna masuk 10 besar
            if ($topScores->count() < 10 || $screamScale > $topScores->last()) {
                Leaderboard::create([
                    'name' => $name,
                    'scream_scale' => $screamScale,
                ]);
            }
        }

        // Ambil data leaderboard terbaru
        $leaderboards = Leaderboard::orderBy('scream_scale', 'desc')->take(10)->get();

        return response()->json($leaderboards);
    }
}
