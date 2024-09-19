<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil data dari tabel leaderboard, urutkan berdasarkan scream_scale tertinggi dengan pagination
        $leaderboards = Leaderboard::orderBy('scream_scale', 'desc')->paginate(10);

        // Kirim data ke view
        return view('index', compact('leaderboards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validasi input
    $request->validate([
        'name' => 'required',
        'scream_scale' => 'required|integer',
    ]);

    // Simpan data teriakan ke dalam database
    Leaderboard::create([
        'name' => $request->name,
        'scream_scale' => $request->scream_scale,
    ]);

    // Ambil data leaderboard terbaru yang sudah diurutkan
    $leaderboards = Leaderboard::orderBy('scream_scale', 'desc')->get();

    // Kembalikan data dalam bentuk JSON untuk diupdate di frontend
    return response()->json($leaderboards);
}
}
