<!-- Edit -->
<div class="modal fade" id="edit{{ $schedule->slug }}">
    <div class=" modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('app.close') }}">
                    <span aria-hidden="true">&times;</span></button>
            </div>
            <h4 class="modal-title"><b>{{ __('app.update_schedule') }}</b></h4>
            <div class="modal-body text-left">
                <form class="form-horizontal" method="POST" action="{{ route('schedule.update', $schedule->slug) }}">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">{{ __('app.name') }}</label>
                        <div class="bootstrap-timepicker">
                            <input type="text" class="form-control timepicker" id="name" name="slug"
                                value="{{ $schedule->slug }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_time_in" class="col-sm-3 control-label">{{ __('app.time_in') }}</label>
                        <div class="bootstrap-timepicker">
                            <input type="time" class="form-control" id="edit_time_in" name="time_in"
                                value="{{ \Carbon\Carbon::parse($schedule->time_in)->format('H:i') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_time_out" class="col-sm-3 control-label">{{ __('app.time_out') }}</label>
                        <div class="bootstrap-timepicker">
                            <input type="time" class="form-control" id="edit_time_out" name="time_out"
                                value="{{ \Carbon\Carbon::parse($schedule->time_out)->format('H:i') }}">
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i
                        class="fa fa-close"></i> {{ __('app.close') }}</button>
                <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-check-square-o"></i>
                    {{ __('app.update') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Delete -->
<div class="modal fade" id="delete{{ $schedule->slug }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header " style="align-items: center">
                <h4 class="modal-title "><span class="employee_id">{{ __('app.delete_schedule') }}</span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('app.close') }}"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="{{ route('schedule.destroy', $schedule->slug) }}">
                    @csrf
                    {{ method_field('DELETE') }}
                    <div class="text-center">
                        <h6>{{ __('app.confirm_delete_item') }}</h6>
                        <h2 class="bold del_employee_name">{{ $schedule->slug }}</h2>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i
                        class="fa fa-close"></i> {{ __('app.close') }}</button>
                <button type="submit" class="btn btn-danger btn-flat"><i class="fa fa-trash"></i> {{ __('app.delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
