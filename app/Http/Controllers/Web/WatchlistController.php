<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Kelas WatchlistController: watchlist controller
class WatchlistController extends Controller
{
    
    // toggle
    public function toggle(Request $request, ?int $countryId = null)
    {
        $countryId = $countryId ?? $request->input('country_id');

        $request->merge(['country_id' => $countryId]);
        $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
        ]);

        $userId = Auth::id();

        $existing = Watchlist::where('user_id', $userId)
            ->where('country_id', $countryId)
            ->first();

        $country = Country::find($countryId);

        if ($existing) {
            $existing->delete();

            \App\Models\ActivityLog::record('watchlist.removed', $country, $userId);

            return response()->json([
                'success' => true,
                'watching' => false,
                'message' => 'Country removed from watchlist.',
            ]);
        }

        Watchlist::create([
            'user_id' => $userId,
            'country_id' => $countryId,
            'is_active' => true,
        ]);

        \App\Models\ActivityLog::record('watchlist.added', $country, $userId);

        return response()->json([
            'success' => true,
            'watching' => true,
            'message' => 'Country added to watchlist.',
        ]);
    }

    
    // remove
    public function remove(Request $request)
    {
        $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
        ]);

        Watchlist::where('user_id', Auth::id())
            ->where('country_id', $request->input('country_id'))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Country removed from watchlist.',
        ]);
    }
}
