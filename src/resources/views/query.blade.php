@extends('web::layouts.grids.12')

@section('title', trans('pap::pap.myPapRecords'))
@section('page_header', trans('pap::pap.myPapRecords'))

@push('head')
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('pap::pap.totalPointsLast30Days') }}</h3>
                </div>
                <div class="card-body">
                    <h3 class="text-center">{{ number_format($totalPointsLast30Days) }} Points</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('pap::pap.totalPointsCurrentMonth') }}</h3>
                </div>
                <div class="card-body">
                    <h3 class="text-center">{{ number_format($totalPointsCurrentMonth) }} Points</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('pap::pap.filterRecords') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('pap.query') }}" method="GET" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="startDate" class="mr-2">{{ trans('pap::pap.startDate') }}:</label>
                            <input type="date" id="startDate" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="form-group mr-3">
                            <label for="endDate" class="mr-2">{{ trans('pap::pap.endDate') }}:</label>
                            <input type="date" id="endDate" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <button type="submit" class="btn btn-primary">{{ trans('pap::pap.filter') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('pap::pap.myPapRecords') }}</h3>
                </div>
                <div class="card-body">
                    <table id="pap-records-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('pap::pap.fleetId') }}</th>
                                <th>{{ trans('pap::pap.charName') }}</th>
                                <th>{{ trans('pap::pap.shipName') }}</th>
                                <th>{{ trans('pap::pap.solarSystemName') }}</th>
                                <th>{{ trans('pap::pap.joinTime') }}</th>
                                <th>{{ trans('pap::pap.point') }}</th>
                                <th>{{ trans('pap::pap.createdAt') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($charRecords as $record)
                            <tr>
                                <td>{{ $record->fleet_id }}</td>
                                <td>
                                    <a href="/characters/{{ $record->character_id }}/sheet" target="_blank">{{ $record->character_name }}</a>
                                </td>
                                <td>
                                    <a href="https://everef.net/types/{{ $record->ship_type_id }}" target="_blank" rel="noopener noreferrer">
                                        <img src="https://images.evetech.net/types/{{ $record->ship_type_id }}/icon?size=32"
                                            alt="{{ $record->ship_type_name }}"
                                            title="{{ $record->ship_type_name }}"
                                            class="eve-icon mr-1" style="vertical-align: middle; height: 24px; width: 24px;">
                                        {{ $record->ship_type_name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="https://evemaps.dotlan.net/system/{{ $record->solar_system_name }}" target="_blank" rel="noopener noreferrer">
                                        {{ $record->solar_system_name }}
                                    </a>
                                </td>
                                <td>{{ $record->join_time }}</td>
                                <td>{{ $record->point }}</td>
                                <td data-order="{{ strtotime($record->created_at) }}">
                                    <span data-toggle="tooltip" data-placement="top" title="{{ $record->created_at }}">{{ human_diff($record->created_at) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
