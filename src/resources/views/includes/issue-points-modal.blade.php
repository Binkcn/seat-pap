<div class="modal fade" tabindex="-1" role="dialog" id="issue-points-edit">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('pap::pap.issuePoints') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="fleet-records-content">
                    <p class="text-muted text-center">Loading fleet records...</p>
                </div>
                <hr>

                <form role="form" action="{{ route('papadmin.issuePoints', 0) }}" method="post">
                    {{ csrf_field() }}
                    <input type="submit" class="btn btn-primary" id="issuePoints" value="{{ trans('pap::pap.issuePoints') }}" />
                </form>
            </div>
        </div>
    </div>
</div>