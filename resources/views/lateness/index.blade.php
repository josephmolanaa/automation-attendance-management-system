@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    @include('includes.datatable-controls-style')
@endsection

@section('page-title') {{ __('app.lateness_management') }} @endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">{{ __('app.lateness_management') }}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.lateness_management') }}</a></li>
        </ol>
    </div>
@endsection

@section('button')
@endsection

@section('content')
@include('includes.flash')

    <div class="row">
        <div class="col-12">

            {{-- Calculate Card --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('lateness.calculate') }}" class="d-flex flex-wrap align-items-end" style="gap:10px;">
                        @csrf
                        <div>
                            <label>{{ __('app.calculation_period') }}</label>
                            <input type="month" name="month" class="form-control" value="{{ sprintf('%04d-%02d', $filters['tahun'], $filters['bulan']) }}">
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
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="force" name="force" value="1">
                            <label class="custom-control-label" for="force">{{ __('app.recalculate') }}</label>
                        </div>
                        <button class="btn btn-primary">
                            <i class="mdi mdi-calculator mr-1"></i> {{ __('app.calculate_lateness') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="management-filter-bar d-flex flex-wrap">
                <div>
                    <label>{{ __('app.month') }}</label>
                    <select id="filterMonth" class="form-control">
                        <option value="">{{ __('app.all_months') }}</option>
                        <option value="01">{{ __('app.january') }}</option>
                        <option value="02">{{ __('app.february') }}</option>
                        <option value="03">{{ __('app.march') }}</option>
                        <option value="04">{{ __('app.april') }}</option>
                        <option value="05">{{ __('app.may') }}</option>
                        <option value="06">{{ __('app.june') }}</option>
                        <option value="07">{{ __('app.july') }}</option>
                        <option value="08">{{ __('app.august') }}</option>
                        <option value="09">{{ __('app.september') }}</option>
                        <option value="10">{{ __('app.october') }}</option>
                        <option value="11">{{ __('app.november') }}</option>
                        <option value="12">{{ __('app.december') }}</option>
                    </select>
                </div>
                <div>
                    <label>{{ __('app.year') }}</label>
                    <select id="filterYear" class="form-control">
                        <option value="">{{ __('app.all_years') }}</option>
                        @foreach(range(date('Y'), 2024) as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>{{ __('app.status') }}</label>
                    <select id="filterStatus" class="form-control">
                        <option value="">{{ __('app.all_status') }}</option>
                        <option value="terlambat">{{ __('app.status_late') }}</option>
                        <option value="tepat_waktu">{{ __('app.status_on_time') }}</option>
                        <option value="tidak_ada_scan">{{ __('app.status_no_scan') }}</option>
                    </select>
                </div>
                <div>
                    <label>{{ __('app.employee') }}</label>
                    <select id="filterEmployee" class="form-control" style="min-width:180px;">
                        <option value="">{{ __('app.all_employees') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>&nbsp;</label>
                    <button id="btnReset" class="btn btn-secondary d-block">{{ __('app.reset') }}</button>
                </div>
                <div class="ml-auto d-flex align-items-end" style="gap:8px;">
                    <a class="btn btn-outline-primary" href="{{ route('lateness.recap', request()->query()) }}">
                        <i class="mdi mdi-chart-bar mr-1"></i>{{ __('app.view_recap') }}
                    </a>
                    <a class="btn btn-success" href="{{ route('lateness.export', request()->query()) }}">
                        <i class="mdi mdi-file-excel mr-1"></i> {{ __('app.export_excel') }}
                    </a>
                </div>
            </div>

            {{-- Data Table Card --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="lateness-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.nip') }}</th>
                                        <th>{{ __('app.name') }}</th>
                                        <th>{{ __('app.position') }}</th>
                                        <th>{{ __('app.date') }}</th>
                                        <th>{{ __('app.day') }}</th>
                                        <th>{{ __('app.scan_in') }}</th>
                                        <th>{{ __('app.scan_out') }}</th>
                                        <th>{{ __('app.shift') }}</th>
                                        <th>{{ __('app.schedule_in') }}</th>
                                        <th>{{ __('app.status') }}</th>
                                        <th>{{ __('app.late_duration') }}</th>
                                        <th>{{ __('app.late_minutes') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
<script>
$(function() {
    var table = $('#lateness-table').DataTable({
        destroy: true,
        processing: false,
        serverSide: false,
        ajax: {
            url: '{{ route("lateness.data") }}',
            type: 'GET',
            data: function(d) {
                d.bulan    = $('#filterMonth').val();
                d.tahun    = $('#filterYear').val();
                d.status   = $('#filterStatus').val();
                d.employee = $('#filterEmployee').val();
            }
        },
        columns: [
            { data: 'nip' },
            { data: 'name' },
            { data: 'position' },
            { data: 'date' },
            { data: 'day' },
            { data: 'scan_in' },
            { data: 'scan_out' },
            { data: 'shift' },
            { data: 'schedule_in' },
            { data: 'status', orderable: false },
            { data: 'late_duration', orderable: false },
            { data: 'late_minutes' },
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, '{{ __('app.all') }}']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lBf>rtip',
        buttons: [
            { extend: 'copy',  text: '<i class="mdi mdi-content-copy mr-1"></i> {{ __('app.copy') }}',  className: 'btn btn-sm btn-secondary' },
            { extend: 'excel', text: '<i class="mdi mdi-file-excel mr-1"></i> Excel',   className: 'btn btn-sm btn-success', title: '{{ __('app.lateness_data') }}' },
            { extend: 'pdf',   text: '<i class="mdi mdi-file-pdf mr-1"></i> PDF',       className: 'btn btn-sm btn-danger',  title: '{{ __('app.lateness_data') }}', orientation: 'landscape', pageSize: 'A4' },
        ],
        order: [[3, 'desc']],
        language: window.DataTableLang,
    });

    $('#filterMonth, #filterYear, #filterStatus, #filterEmployee').on('change', function() {
        table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
        $('#filterMonth').val('');
        $('#filterYear').val('{{ date("Y") }}');
        $('#filterStatus').val('');
        $('#filterEmployee').val('');
        table.ajax.reload();
    });

    // Set initial filters from URL params
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('bulan')) {
        $('#filterMonth').val(String(urlParams.get('bulan')).padStart(2, '0'));
    }
    if (urlParams.get('tahun')) {
        $('#filterYear').val(urlParams.get('tahun'));
    }
    if (urlParams.get('status')) {
        $('#filterStatus').val(urlParams.get('status'));
    }
    if (urlParams.get('employee')) {
        $('#filterEmployee').val(urlParams.get('employee'));
    }
    table.ajax.reload();
});
</script>
@endsection
