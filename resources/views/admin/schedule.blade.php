@extends('layouts.master')

@section('css')
    <!-- Table css -->
    <link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet"
        type="text/css" media="screen">
    @include('includes.datatable-controls-style')
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">{{ __('app.schedules') }}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_schedule') }}</a></li>
        </ol>
    </div>
@endsection

@section('button')
    <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="mdi mdi-plus mr-2"></i>{{ __('app.add') }}</a>
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="schedule-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.id') }}</th>
                                        <th>{{ __('app.shift') }}</th>
                                        <th>{{ __('app.time_in') }}</th>
                                        <th>{{ __('app.time_out') }}</th>
                                        <th>{{ __('app.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($schedules as $schedule)
                                        <tr>
                                            <td> {{ $schedule->id }} </td>
                                            <td> {{ $schedule->slug }} </td>
                                            <td> {{ $schedule->time_in }} </td>
                                            <td> {{ $schedule->time_out }} </td>
                                            <td>
                                                <a href="#edit{{ $schedule->slug }}" data-toggle="modal"
                                                    class="btn btn-success btn-sm edit btn-flat"><i class='fa fa-edit'></i> {{ __('app.edit') }}</a>
                                                <a href="#delete{{ $schedule->slug }}" data-toggle="modal"
                                                    class="btn btn-danger btn-sm delete btn-flat"><i class='fa fa-trash'></i> {{ __('app.delete') }}</a>
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
    </div>

    @foreach ($schedules as $schedule)
        @include('includes.edit_delete_schedule')
    @endforeach

    @include('includes.add_schedule')

@endsection

@section('script-bottom')
    <script>
        $(function() {
            $('#schedule-table').DataTable({
                destroy: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, '{{ __('app.all') }}']],
                dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
                order: [[0, 'asc']],
                language: window.DataTableLang,
            });
        });
    </script>
@endsection
