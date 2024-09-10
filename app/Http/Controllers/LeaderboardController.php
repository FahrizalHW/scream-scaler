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
    // Ambil data dari tabel leaderboard, urutkan berdasarkan scream_scale tertinggi
    $leaderboards = Leaderboard::orderBy('scream_scale', 'desc')->get();

    // Kirim data ke view
    return view('index', compact('leaderboards'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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

    Leaderboard::create([
        'name' => $request->name,
        'scream_scale' => $request->scream_scale,
    ]);

    return redirect()->back()->with('success', 'Teriakan berhasil disimpan!');
}


    /**
     * Display the specified resource.
     */
    public function show(Leaderboard $leaderboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leaderboard $leaderboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Leaderboard $leaderboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leaderboard $leaderboard)
    {
        //
    }
}
