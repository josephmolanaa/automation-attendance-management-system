<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('app.close') }}">
                    <span aria-hidden="true">&times;</span></button>

            </div>

            <h4 class="modal-title"><b>{{ __('app.add_employee') }}</b></h4>
            <div class="modal-body">

                <div class="card-body text-left">

                    <form method="POST" action="{{ route('employees.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="emp_id">{{ __('app.employee_id') }}</label>
                            <input type="text" class="form-control" placeholder="{{ __('app.enter_employee_id') }}" id="emp_id" name="emp_id"
                                required />
                        </div>
                        <div class="form-group">
                            <label for="name">{{ __('app.name') }}</label>
                            <input type="text" class="form-control" placeholder="{{ __('app.enter_employee_name') }}" id="name" name="name"
                                required />
                        </div>
                        <div class="form-group">
                            <label for="position">{{ __('app.position') }}</label>
                            <input type="text" class="form-control" placeholder="{{ __('app.enter_position') }}" id="position" name="position"
                                required />
                        </div>

                        
                        <div class="form-group">
                            <label for="email" class="col-sm-3 control-label">{{ __('app.email') }}</label>


                            <input type="email" class="form-control" id="email" name="email">

                        </div>
                        <div class="form-group">
                            <label for="schedule" class="col-sm-3 control-label">{{ __('app.schedule') }}</label>


                            <select class="form-control" id="schedule" name="schedule" required>
                                <option value="" selected>- {{ __('app.select') }} -</option>
                                @foreach($schedules as $schedule)
                                <option value="{{$schedule->slug}}">{{$schedule->slug}} -> {{ __('app.from') }} {{$schedule->time_in}}
                                    {{ __('app.to_time') }} {{$schedule->time_out}} </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="form-group">
                            <div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    {{ __('app.submit') }}
                                </button>
                                <button type="reset" class="btn btn-secondary waves-effect m-l-5" data-dismiss="modal">
                                    {{ __('app.cancel') }}
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>


        </div>

    </div>
</div>
</div>
