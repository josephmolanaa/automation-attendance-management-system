@extends('layouts.master')

@section('css')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    @include('includes.datatable-controls-style')
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">{{ __('app.sheet_report') }}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.sheet_report') }}</a></li>
        </ol>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Filter Bar --}}
            <div class="management-filter-bar d-flex flex-wrap">
                <div>
                    <label>{{ __('app.month') }}</label>
                    <select id="filterMonth" class="form-control">
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
                        @foreach(range(date('Y'), 2024) as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>&nbsp;</label>
                    <button id="btnReset" class="btn btn-secondary d-block">{{ __('app.reset') }}</button>
                </div>
                <div>
                    <label>&nbsp;</label>
                    <button id="btnExport" class="btn btn-success d-block">
                        <i class="mdi mdi-file-excel mr-1"></i> {{ __('app.export_excel') }}
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="sheet-report-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:13px;">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.id') }}</th>
                                        <th>{{ __('app.name') }}</th>
                                        <th>{{ __('app.position') }}</th>
                                        <th>{{ __('app.day') }}</th>
                                        <th>{{ __('app.date') }}</th>
                                        <th>{{ __('app.scan_1') }}</th>
                                        <th>{{ __('app.scan_2') }}</th>
                                        <th>{{ __('app.scan_3') }}</th>
                                        <th>{{ __('app.late_time') }}</th>
                                        <th>{{ __('app.normal') }}</th>
                                        <th>{{ __('app.double') }}</th>
                                        <th>{{ __('app.sunday_label') }}</th>
                                        <th>{{ __('app.izin_cuti') }}</th>
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
    // Set default bulan = bulan sekarang
    var nowMonth = ('0' + (new Date().getMonth() + 1)).slice(-2);
    $('#filterMonth').val(nowMonth);

    var table = $('#sheet-report-table').DataTable({
        processing: false,
        serverSide: false,
        ajax: {
            url: '/sheet-report/data',
            data: function(d) {
                d.bulan = $('#filterMonth').val();
                d.tahun = $('#filterYear').val();
            }
        },
        columns: [
            { data: 'emp_id' },
            { data: 'name' },
            { data: 'position' },
            { data: 'hari' },
            { data: 'tanggal' },
            { data: 'scan_1' },
            { data: 'scan_2' },
            { data: 'scan_3' },
            { data: 'late_time' },
            {
                data: 'normal',
                render: function(val) {
                    return val && val !== '-' ? '<span class="badge badge-info" style="font-size:12px;padding:4px 8px">' + val + '</span>' : '-';
                }
            },
            {
                data: 'double',
                render: function(val) {
                    return val && val !== '-' ? '<span class="badge badge-warning" style="font-size:12px;padding:4px 8px">' + val + '</span>' : '-';
                }
            },
            {
                data: 'minggu',
                render: function(val) {
                    return val === 1 ? '<span class="badge badge-success" style="font-size:12px;padding:4px 8px">1</span>' : '-';
                }
            },
            {
                data: 'izin_cuti',
                render: function(val) {
                    if (!val || val === '-') return '-';
                    var colors = { 'Sakit': 'danger', 'Izin': 'warning', 'Cuti': 'info', 'Dinas': 'primary' };
                    var color = colors[val] || 'secondary';
                    return '<span class="badge badge-' + color + ' badge-pill">' + val + '</span>';
                }
            },
        ],
        order: [[4, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, '{{ __('app.all') }}']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
        buttons: [...amsExportButtons('{{ __('app.sheet_report_data') }}')],
        language: window.DataTableLang,
    });

    $('#btnExport').on('click', function() {
        var bulan = $('#filterMonth').val();
        var tahun = $('#filterYear').val();
        window.location.href = '/sheet-report/export?bulan=' + bulan + '&tahun=' + tahun;
    });

    $('#btnLoad').on('click', function() {
    table.ajax.reload();
    });

    $('#filterMonth, #filterYear').on('change', function() {
        table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
        var nowMonth = ('0' + (new Date().getMonth() + 1)).slice(-2);
        $('#filterMonth').val(nowMonth);
        $('#filterYear').val('{{ date("Y") }}');
        table.ajax.reload();
    });
});
</script>
@endsection
