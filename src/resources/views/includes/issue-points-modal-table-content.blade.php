
@if ($fleetRecords->isEmpty())
    <p class="text-muted text-center">No fleet records found for this fleet.</p>
@else
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>{{ trans('pap::pap.fleetId') }}</th>
                <th>{{ trans('pap::pap.members') }}</th>
                <th>{{ trans('pap::pap.point') }}</th>
                <th>{{ trans('pap::pap.createdAt') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($fleetRecords as $record)
                <tr>
                    <td>{{ $record->fleet_id }}</td>
                    <td>{{ $record->fleet_members_count }}</td>
                    <td>{{ $record->point }}</td>

                    <td data-order="{{ strtotime($record->created_at) }}">
                        <span data-toggle="tooltip" data-placement="top" title="{{ $record->created_at }}">{{ human_diff($record->created_at) }}</span>
                    </td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>
@endif