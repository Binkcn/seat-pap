<?php

namespace Binkcn\Seat\SeatPap\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Binkcn\Seat\SeatPap\Models\CharRecords;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Web\Http\Controllers\Controller;

class PapController extends Controller
{
    public function papGetRequests(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors('You must be logged in.');
        }


        $startDate = $request->input('start_date') 
                     ? Carbon::parse($request->input('start_date'))->startOfDay() 
                     : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') 
                   ? Carbon::parse($request->input('end_date'))->endOfDay() 
                   : Carbon::now()->endOfMonth();

        $characterIds = $user->characters->pluck('character_id')->toArray();
        $charRecords = CharRecords::whereIn('character_id', $characterIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $last30DaysStart = Carbon::now()->subDays(30)->startOfDay();
        $totalPointsLast30Days = CharRecords::whereIn('character_id', $characterIds)
            ->whereBetween('created_at', [$last30DaysStart, Carbon::now()->endOfDay()])
            ->sum('point');

        $currentMonthStart = Carbon::now()->startOfMonth();
        $totalPointsCurrentMonth = CharRecords::whereIn('character_id', $characterIds)
            ->whereBetween('created_at', [$currentMonthStart, Carbon::now()->endOfMonth()])
            ->sum('point');

        // Collect all unique ship_type_ids and solar_system_ids to avoid N+1 queries
        $shipTypeIds = $charRecords->pluck('ship_type_id')->unique();
        $solarSystemIds = $charRecords->pluck('solar_system_id')->unique();

        // Fetch ship names
        $shipTypes = InvType::whereIn('typeID', $shipTypeIds)
                            ->pluck('typeName', 'typeID')
                            ->toArray();

        // Fetch solar system names
        $solarSystems = SolarSystem::whereIn('system_id', $solarSystemIds)
                                      ->pluck('name', 'system_id')
                                      ->toArray();

        // Add names to each record object
        foreach ($charRecords as $record) {
            $record->ship_type_name = $shipTypes[$record->ship_type_id] ?? 'Unknown Ship';
            $record->solar_system_name = $solarSystems[$record->solar_system_id] ?? 'Unknown System';
        }

        return view('pap::query', compact(
            'charRecords',
            'totalPointsLast30Days',
            'totalPointsCurrentMonth',
            'startDate',
            'endDate'
        ));
    }
}
