<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReadingGoalController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reading_goal' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $request->user()->update([
            'reading_goal' => $data['reading_goal'],
            'reading_goal_year' => now()->year,
        ]);

        return back()->with('success', now()->year.' okuma hedefin '.$data['reading_goal'].' kitap olarak kaydedildi! 📚');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->update([
            'reading_goal' => null,
            'reading_goal_year' => null,
        ]);

        return back()->with('success', 'Okuma hedefin kaldırıldı.');
    }
}
