@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <style>
        .dataTables_length,
        .dataTables_filter {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-size: 15px !important;
        }
        .dataTables_length label,
        .dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-bottom: 0 !important;
            font-size: 15px !important;
            white-space: nowrap !important;
        }
        .dataTables_length select {
            height: 38px !important;
            font-size: 15px !important;
            width: 80px !important;
            padding: 4px 8px !important;
            border-radius: 6px !important;
            border: 1px solid #ced4da !important;
            background-image: none !important;
            -webkit-appearance: auto !important;
            -moz-appearance: auto !important;
            appearance: auto !important;
        }
        .dataTables_filter input {
            height: 38px !important;
            font-size: 15px !important;
            padding: 4px 10px !important;
            border-radius: 6px !important;
            border: 1px solid #ced4da !important;
        }
        .dt-buttons { display: flex !important; align-items: center !important; gap: 6px !important; }
        .dt-buttons .btn { height: 38px !important; font-size: 14px !important; display: flex !important; align-items: center !important; }
    </style>
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Employees</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">Employees</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">Employees List</a></li>
    </ol>
</div>
@endsection

@section('button')
<a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="mdi mdi-plus mr-2"></i>Add</a>
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
                <table id="employee-table" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Schedule</th>
                            <th>Member Since</th>
                            <th>Actions</th>
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
                                <a href="#edit{{ $employee->id }}" data-toggle="modal" class="btn btn-success btn-sm btn-flat"><i class='fa fa-edit'></i> Edit</a>
                                <a href="#delete{{ $employee->id }}" data-toggle="modal" class="btn btn-danger btn-sm btn-flat"><i class='fa fa-trash'></i> Delete</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',

        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            search: 'Cari:',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 data',
            paginate: { next: 'Selanjutnya', previous: 'Sebelumnya' }
        }
    });
});
</script>
@endsection