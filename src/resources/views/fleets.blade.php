@extends('web::layouts.grids.12')

@section('title', trans('pap::pap.fleets'))
@section('page_header', trans('pap::pap.fleets'))

@push('head')
@endpush

@section('full')
<div class="card card-primary card-solid">
    <div class="card-header">
        <h3 class="card-title">{{ trans('pap::pap.fleets') }}</h3>

        <div class="btn-group float-right" role="group">
            <input type="button" class="btn btn-primary" id="readFleet" name="readFleet" value="{{ trans('pap::pap.sync_fleet_esi') }}" />
        </div>
    </div>
    <div class="card-body">
        <table id="fleets" class="table table-bordered">
            <thead>
                <tr>
                <th>{{ trans('pap::pap.fleetId') }}</th>
                <th>{{ trans('pap::pap.fleetFC') }}</th>
                <th>{{ trans('pap::pap.fleetMoTD') }}</th>
                <th>{{ trans('pap::pap.createdAt') }}</th>
                <th>{{ trans('pap::pap.fleetAvailable') }}</th>
                <th>{{ trans('pap::pap.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fleets as $fleet)
                <tr>
                <td>
                    {{ $fleet->fleet_id }}
                </td>
                <td>
                    <a href="/characters/{{ $fleet->fc_character_id }}/sheet" target="_blank">{{ $fleet->fc_character_name }}</a>
                </td>
                <td>{!! $fleet->fleet_motd !!}</td>
                <td data-order="{{ strtotime($fleet->created_at) }}">
                    <span data-toggle="tooltip" data-placement="top" title="{{ $fleet->created_at }}">{{ human_diff($fleet->created_at) }}</span>
                </td>
                <td>
                    @if ($fleet->fleet_available === 0)
                    <span class="badge badge-warning">{{ trans('pap::pap.fleetDown') }}</span>
                    @elseif ($fleet->fleet_available === 1)
                    <span class="badge badge-success">{{ trans('pap::pap.fleetActive') }}</span>
                    @endif
                </td>
                <td>
                    @if ($fleet->fleet_available === 1 && $fleet->fc_character_id === auth()->user()->main_character_id)
                    <button class="btn btn-xs btn-link" data-toggle="modal" data-target="#issue-points-edit" data-fleet-id="{{ $fleet->fleet_id }}">
                        <i class="fas fa-trophy"></i>
                        {{ trans('pap::pap.issuePoints') }}
                    </button>
                    @else
                    <span class="text-muted">No actions available</span>
                    @endif
                </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('pap::includes.issue-points-modal')
</div>
@stop

@push('javascript')
@include('web::includes.javascript.id-to-name')

<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<script type="application/javascript">

  $(function () {
$('#fleets').DataTable({
    ordering: false
});

    $('#readFleet').on('click', function() {
	var $button = $(this);
	$button.prop('disabled', true);

        $.ajax({
            headers: function() {},
            url: "{{ route('papadmin.getFleetInfo') }}",
            // dataType: 'json',
            data: '',
            timeout: 10000,
        }).done(function(result) {
            // $('.overlay').hide();

            if (result) {
                alert('Fleet information synchronized successfully.');
		window.location.reload();
            } else {
                alert('Failed to synchronize fleet information.');
                // $('.overlay').hide();
            }
        }).fail(function(e) {
            // $('.overlay').hide();

            alert(e.responseText);
		window.location.reload();
        }).always(function() {
		$button.prop('disabled', false);
        });
    });

    $('#issue-points-edit').on('show.bs.modal', function(e){
        var modal = $(this);
        var fleetId = $(e.relatedTarget).attr('data-fleet-id');
        var link = "{{ route('papadmin.getFleetRecords', ':fleet_id') }}";
        link = link.replace(':fleet_id', fleetId);

        var contentContainer = modal.find('#fleet-records-content');

        contentContainer.html('<p class="text-muted text-center"><i class="fas fa-spinner fa-spin"></i> Loading fleet records...</p>');

        $.ajax({
            url: link,
            dataType: 'html',
            method: 'GET'
        }).done(function(responseHtml){
            contentContainer.html(responseHtml);
        }).fail(function(jqXHR, status){
            contentContainer.html(`<p class="text-danger text-center">Failed to load fleet records: ${status}</p>`);
            console.error('AJAX Error:', jqXHR.responseText || status);
        }).always(function(){
        });

        var form = modal.find('form');
        var routeIssuePointsBase = "{{ route('papadmin.issuePoints', ':fleet_id') }}";
        var newActionUrl = routeIssuePointsBase.replace(':fleet_id', fleetId);

        form.attr('action', newActionUrl);
    });

    $('#issue-points-edit form').on('submit', function() {
        var $form = $(this);
        var $submitButton = $form.find('#issuePoints');

        $submitButton.prop('disabled', true);
    });

    $('#issue-points-edit').on('hidden.bs.modal', function () {
        var $form = $(this).find('form');
        var $submitButton = $form.find('#issuePoints');
        $submitButton.prop('disabled', false);
    });

    $('#issue-points-edit').on('hidden.bs.modal', function() {
        $(this).find('#fleet-records-content').html('<p class="text-muted text-center">Loading fleet records...</p>');
    });
});
</script>
@endpush
