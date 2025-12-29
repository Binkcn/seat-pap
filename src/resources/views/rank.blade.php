@extends('web::layouts.grids.12')

@section('title', trans('pap::pap.userRankings'))
@section('page_header', trans('pap::pap.userRankings'))

@push('head')
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ trans('pap::pap.filterRecords') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('papadmin.rank') }}" method="GET" class="form-inline">
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
                    <h3 class="card-title">{{ trans('pap::pap.userRankings') }}</h3>
                </div>
                <div class="card-body">
                    <table id="pap-records-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('pap::pap.rank') }}</th>
                                <th>{{ trans('pap::pap.userName') }}</th>
                                <th>{{ trans('pap::pap.mainCharacterName') }}</th>
				                <th>{{ trans('pap::pap.boundCharactersCount') }}</th>
                                <th>{{ trans('pap::pap.totalPoints') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $rankOffset = ($usersPapRecords->currentPage() - 1) * $usersPapRecords->perPage();
                            @endphp
                            @foreach ($usersPapRecords as $index => $record)
                            <tr>
                                <td>{{ $rankOffset + $index + 1 }}</td> {{-- 显示排名 --}}
                                <td>{{ $record->user_name }}</td>
                                <td>
                                    @if ($record->main_character_id && $record->main_character_name !== 'N/A')
                                        <a href="/characters/{{ $record->main_character_id }}/sheet" target="_blank">{{ $record->main_character_name }}</a>
                                    @else
                                        {{ $record->main_character_name }}
                                    @endif
                                </td>
				                <td>{{ $record->bound_characters_count }}</td>
                                <td>{{ number_format($record->total_pap_points) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
		            <hr />
                    <div class="d-flex justify-content-center">
                        {{ $usersPapRecords->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
