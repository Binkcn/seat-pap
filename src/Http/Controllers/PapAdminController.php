<?php

namespace Binkcn\Seat\SeatPap\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Binkcn\Seat\SeatPap\Models\Fleets;
use Binkcn\Seat\SeatPap\Models\FleetRecords;
use Binkcn\Seat\SeatPap\Models\CharRecords;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Services\EseyeClient;
use Seat\Web\Models\User;
use Seat\Web\Http\Controllers\Controller;

class PapAdminController extends Controller
{
    public function papGetRank(Request $request)
    {
        $startDate = $request->input('start_date')
                     ? Carbon::parse($request->input('start_date'))->startOfDay()
                     : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date')
                   ? Carbon::parse($request->input('end_date'))->endOfDay()
                   : Carbon::now()->endOfMonth();


        $users = User::all();
        $userIds = $users->pluck('id')->toArray();

        $allRefreshTokens = RefreshToken::whereIn('user_id', $userIds)->get();
        $allCharacterIds = $allRefreshTokens->pluck('character_id')->unique()->toArray();

        $charRecords = CharRecords::whereIn('character_id', $allCharacterIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $refreshTokensByUserId = $allRefreshTokens->groupBy('user_id');
        $charRecordsByCharacterId = $charRecords->groupBy('character_id');

        $aggregatedUsers = collect();
        foreach ($users as $user) {
            $totalPapPoints = 0;
            $userRefreshTokens = $refreshTokensByUserId->get($user->id, collect());
            $boundCharactersCount = $userRefreshTokens->count();

            foreach ($userRefreshTokens as $refreshToken) {
                $recordsForCharacter = $charRecordsByCharacterId->get($refreshToken->character_id, collect());
                $totalPapPoints += $recordsForCharacter->sum('point');
            }

            $aggregatedUsers->push((object)[
                'user_id' => $user->id,
                'user_name' => $user->name,
                'main_character_id' => $user->main_character_id,
                'bound_characters_count' => $boundCharactersCount,
                'total_pap_points' => $totalPapPoints,
                'main_character_name' => null,
            ]);
        }

        $aggregatedUsers = $aggregatedUsers->sortByDesc('total_pap_points')->values();
        $mainCharacterIds = $aggregatedUsers->pluck('main_character_id')->filter()->unique();
        $mainCharacterNames = [];
        if ($mainCharacterIds->isNotEmpty()) {
            $mainCharacterNames = CharacterInfo::whereIn('character_id', $mainCharacterIds)
                ->pluck('name', 'character_id')
                ->toArray();
        }

        $aggregatedUsers = $aggregatedUsers->map(function ($user) use ($mainCharacterNames) {
            $user->main_character_name = $mainCharacterNames[$user->main_character_id] ?? 'N/A';
            return $user;
        });

        $filteredUsers = $aggregatedUsers->filter(function ($user) {
            return $user->main_character_name !== 'N/A';
        })->values();

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $filteredUsers->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $usersPapRecords = new LengthAwarePaginator(
            $currentItems,
            $filteredUsers->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pap::rank', compact(
            'usersPapRecords',
            'startDate',
            'endDate'
        ));
    }

    public function papGetFleets()
    {
        $fleets = Fleets::orderby('created_at', 'desc')
            ->take(10)
            ->get();

        return view('pap::fleets', compact('fleets'));
    }

    public function papGetFleetInfo()
    {
        $refresh_token = RefreshToken::where('character_id', auth()->user()->main_character_id)->first();

        $eseyeClient = new EseyeClient();
        $eseyeClient->setAuthentication($refresh_token);

        try {
            $response = $eseyeClient->invoke('get', '/characters/{character_id}/fleet', [
                'character_id' => auth()->user()->main_character_id,
            ]);

            $character_fleet = $response->getBody();
            if ($character_fleet->fleet_boss_id !== auth()->user()->main_character_id)
                return redirect()->back()
                    ->with('error', trans('pap::pap.not_fleet_boss'));
                 
            if ($character_fleet->role !== 'fleet_commander')
                return redirect()->back()
                    ->with('error', trans('pap::pap.not_fleet_commander')); 

            $response = $eseyeClient->invoke('get', '/fleets/{fleet_id}', [
                'fleet_id' => $character_fleet->fleet_id,
            ]);
            
            $fleet_info = $response->getBody();

            $updated_fleet = Fleets::updateOrCreate(
                [
                    'fleet_id' => $character_fleet->fleet_id,
                ],
                [
                    'fc_character_id' => auth()->user()->main_character_id,
                    'fc_character_name' => auth()->user()->main_character->name,
                    'fleet_motd' => $fleet_info->motd,
                    'fleet_available' => true,
                ]
            );
            $updated_fleet->save();

            return response('Added/Updated Fleet', 200);
        } catch (RequestFailedException $exception) {
            $response = $exception->getEsiResponse();

            if ($response->getErrorCode() == 404 && $response->error() == 'Character is not in a fleet') {
                // not in fleet
                Fleets::where('fc_character_id', auth()->user()->main_character_id)
                    ->update(['fleet_available' => false]);
            }

            return response($response->error(), $response->getErrorCode());
        }
    }

    public function papGetFleetRecords($fleet_id)
    {
        $fleetRecords = FleetRecords::where('fleet_id', $fleet_id)
            ->orderby('created_at', 'desc')
            ->take(20)
            ->get();

        return view('pap::includes.issue-points-modal-table-content', compact('fleetRecords'));
    }

    public function papIssuePoints($fleet_id)
    {
        $fleet = Fleets::where('fleet_id', $fleet_id)->first();
        if ($fleet->fleet_available !== 1)
            return redirect()->back()
                ->with('error', trans('pap::pap.not_fleet_available'));

        if ($fleet->fc_character_id !== auth()->user()->main_character_id)
            return redirect()->back()
                ->with('error', trans('pap::pap.not_fleet_boss'));

        $refresh_token = RefreshToken::where('character_id', auth()->user()->main_character_id)->first();

        $eseyeClient = new EseyeClient();
        $eseyeClient->setAuthentication($refresh_token);

        try {
            $response = $eseyeClient->invoke('get', '/fleets/{fleet_id}/members', [
                'fleet_id' => $fleet_id,
            ]);

            $fleet_members = $response->getBody();
            foreach ($fleet_members as $member) {
                $character_info = CharacterInfo::find($member->character_id);

                $data = [];
                $data['fleet_id'] = $fleet_id;
                $data['character_id'] = $member->character_id;
                $data['character_name'] = $character_info ? $character_info->name : 'Unknown';
                $data['ship_type_id'] = $member->ship_type_id;
                $data['solar_system_id'] = $member->solar_system_id;
                $data['join_time'] = $member->join_time;
                $data['point'] = 1; // default 1 point per member, can be changed later.

                $character_record = CharRecords::create(
                    $data
                );
                $character_record->save();
            }

            $fleet_members_json = json_encode($fleet_members);

            $fleet_record = FleetRecords::create([
                'fleet_id' => $fleet_id,
                'fleet_members' => $fleet_members_json,
                'fleet_members_count' => count($fleet_members),
                'point' => 1, // default 1 point per member, can be changed later.
            ]);
            $fleet_record->save();

            return redirect()->back()
                ->with('success', trans('pap::pap.points_issued'));
        } catch (RequestFailedException $exception) {
            $response = $exception->getEsiResponse();

            if ($response->getErrorCode() == 404 && $response->error() == "The fleet does not exist or you don't have access to it!") {
                Fleets::where('fleet_id', $fleet_id)
                    ->update(['fleet_available' => false]);
            }

            return response($response->error(), $response->getErrorCode());
        }
    }
}
