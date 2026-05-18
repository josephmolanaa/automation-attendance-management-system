@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    @include('includes.datatable-controls-style')
@endsection

@section('page-title') {{ __('app.lateness_recap') }} @endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">{{ __('app.lateness_recap') }}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.lateness_management') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.lateness_recap') }}</a></li>
        </ol>
    </div>
@endsection

@section('button')
@endsection

@section('content')
@include('includes.flash')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3" style="gap:12px;">
                    <form method="GET" action="{{ route('lateness.recap') }}" class="d-flex flex-wrap align-items-end" style="gap:10px;">
                        <div>
                            <label>{{ __('app.month') }}</label>
                            <select name="bulan" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $filters['bulan'] == $m ? 'selected' : '' }}>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label>{{ __('app.year') }}</label>
                            <select name="tahun" class="form-control">
                                @foreach(range(date('Y') + 1, 2024) as $year)
                                    <option value="{{ $year }}" {{ $filters['tahun'] == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>{{ __('app.employee') }}</label>
                            <select name="employee" class="form-control" style="min-width:180px;">
                                <option value="">{{ __('app.all_employees') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ (string) $filters['employee'] === (string) $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button class="btn btn-secondary d-block">{{ __('app.filter') }}</button>
                        </div>
                    </form>

                    <div class="d-flex align-items-end" style="gap:8px;">
                        <a class="btn btn-outline-primary" href="{{ route('lateness.index', request()->query()) }}">
                            <i class="mdi mdi-table mr-1"></i>{{ __('app.view_scanlog') }}
                        </a>
                        <a class="btn btn-success" href="{{ route('lateness.export', request()->query()) }}">
                            <i class="mdi mdi-file-excel mr-1"></i> {{ __('app.export_excel') }}
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>{{ __('app.nip') }}</th>
                                <th>{{ __('app.name') }}</th>
                                <th>{{ __('app.position') }}</th>
                                <th>{{ __('app.total_late_days') }}</th>
                                <th>{{ __('app.total_minutes') }}</th>
                                <th>{{ __('app.total_duration') }}</th>
                                <th>{{ __('app.average_minutes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recap as $row)
                                <tr class="{{ $row['total_late_days'] > 3 ? 'table-danger' : '' }}">
                                    <td>{{ $row['employee']->emp_id ?? $row['employee']->id ?? '-' }}</td>
                                    <td>{{ $row['employee']->name ?? '-' }}</td>
                                    <td>{{ $row['employee']->position ?? '-' }}</td>
                                    <td>{{ $row['total_late_days'] }}</td>
                                    <td>{{ $row['total_minutes'] }}</td>
                                    <td>{{ $row['total_duration'] }}</td>
                                    <td>{{ $row['average_minutes'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">{{ __('app.no_lateness_recap_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
