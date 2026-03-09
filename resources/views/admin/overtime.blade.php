@extends('layouts.master')
@section('css')
    <style>
        /* Hide default processing */
        .dataTables_processing { display: none !important; }

        /* Skeleton loading */
        .skeleton-row td { padding: 8px 10px !important; }
        .skeleton-cell {
            height: 16px;
            border-radius: 4px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
        }
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    <link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet" type="text/css" media="screen">
    <link href="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Over Time</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Over Time</a></li>
        </ol>
    </div>
@endsection

@section('button')
    <a href="/izindancuti" class="btn btn-primary btn-sm btn-flat"><i class="mdi mdi-table mr-2"></i>Leave Table</a>
@endsection

@section('content')
@include('includes.flash')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    {{-- Filter Bar --}}
                    <div class="d-flex flex-wrap mb-3" style="gap:10px">
                        <div>
                            <label>Bulan</label>
                            <select id="filterMonth" class="form-control">
                                <option value="">Semua Bulan</option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div>
                            <label>Tahun</label>
                            <select id="filterYear" class="form-control">
                                <option value="">Semua Tahun</option>
                                @foreach(range(date('Y'), 2024) as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Dari Tanggal</label>
                            <input type="date" id="filterDateFrom" class="form-control">
                        </div>
                        <div>
                            <label>Sampai Tanggal</label>
                            <input type="date" id="filterDateTo" class="form-control">
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button id="btnReset" class="btn btn-secondary d-block">Reset</button>
                        </div>
                    </div>

                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Shift</th>
                                        <th>Schedule Time Out</th>
                                        <th>Actual Time Out</th>
                                        <th>Over Time</th>
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

@section('script')
@endsection

@section('script-bottom')
<script src="{{ URL::asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/dataTables.buttons.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/buttons.html5.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/buttons.print.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('plugins/RWD-Table-Patterns/dist/js/rwd-table.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/pdfmake.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/vfs_fonts.js') }}"></script>
<script>
$(function () {

            // Skeleton loading
            function showSkeleton() {
                var rows = '';
                for (var i = 0; i < 5; i++) {
                    rows += '<tr class="skeleton-row">';
                    for (var j = 0; j < 7; j++) {
                        rows += '<td><div class="skeleton-cell"></div></td>';
                    }
                    rows += '</tr>';
                }
                $('#datatable-buttons tbody').html(rows);
            }

    $('.table-responsive').responsiveTable({
        addDisplayAllBtn: 'btn btn-secondary'
    });

    var table = $('#datatable-buttons').DataTable({
        destroy: true,
        processing: false,
        preDrawCallback: function() { showSkeleton(); },
        ajax: {
            url: '/overtime/data',
            type: 'GET',
            data: function(d) {
                d.bulan  = $('#filterMonth').val();
                d.tahun  = $('#filterYear').val();
                d.dari   = $('#filterDateFrom').val();
                d.sampai = $('#filterDateTo').val();
            }
        },
        columns: [
            { data: 'date' },
            { data: 'emp_id' },
            { data: 'name' },
            { data: 'shift', orderable: false },
            { data: 'schedule_time_out' },
            { data: 'actual_time_out' },
            { data: 'overtime_duration', orderable: false },
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lBf>rtip',
        buttons: [
            { extend: 'copy',  text: '<i class="mdi mdi-content-copy mr-1"></i> Copy',  className: 'btn btn-sm btn-secondary' },
            { extend: 'excel', text: '<i class="mdi mdi-file-excel mr-1"></i> Excel',   className: 'btn btn-sm btn-success', title: 'Overtime Data' },
            { extend: 'pdf',   text: '<i class="mdi mdi-file-pdf mr-1"></i> PDF',       className: 'btn btn-sm btn-danger',  title: 'Overtime Data', orientation: 'landscape', pageSize: 'A4' },
        ],
        order: [[0, 'desc']],
    });

    $('#filterMonth, #filterYear, #filterDateFrom, #filterDateTo').on('change', function() {
        table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
        $('#filterMonth, #filterYear').val('');
        $('#filterDateFrom, #filterDateTo').val('');
        table.ajax.reload();
    });
});
</script>
@endsection