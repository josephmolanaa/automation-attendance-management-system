@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    @include('includes.datatable-controls-style')
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">{{ __('app.employees') }}</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_employees') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.employees_list') }}</a></li>
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
                        <table id="employee-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
                            <thead>
                                <tr>
                                    <th>{{ __('app.employee_id') }}</th>
                                    <th>{{ __('app.name') }}</th>
                                    <th>{{ __('app.position') }}</th>
                                    <th>{{ __('app.email') }}</th>
                                    <th>{{ __('app.schedule') }}</th>
                                    <th>{{ __('app.member_since') }}</th>
                                    <th>{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                <tr>
                                    <td>{{ $employee->emp_id ?? $employee->id }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->position }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>
                                        @if(isset($employee->schedules->first()->slug))
                                            {{ $employee->schedules->first()->slug }}
                                        @endif
                                    </td>
                                    <td>{{ $employee->created_at }}</td>
                                    <td>
                                        <a href="#edit{{ $employee->id }}" data-toggle="modal" class="btn btn-success btn-sm btn-flat"><i class='fa fa-edit'></i> {{ __('app.edit') }}</a>
                                        <a href="#delete{{ $employee->id }}" data-toggle="modal" class="btn btn-danger btn-sm btn-flat"><i class='fa fa-trash'></i> {{ __('app.delete') }}</a>
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

@foreach($employees as $employee)
    @include('includes.edit_delete_employee')
@endforeach

@include('includes.add_employee')

@endsection

@section('script')
@endsection

@section('script-bottom')
<script>
$(function () {
    $('#employee-table').DataTable({
        lengthChange: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, '{{ __('app.all') }}']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
        language: window.DataTableLang,
    });
});
</script>
@endsection
