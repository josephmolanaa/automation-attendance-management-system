@extends('layouts.master')

@section('css')
    <style>
        .dataTables_length label,
        .dataTables_filter label,
        .dataTables_length select,
        .dataTables_filter input {
            font-size: 14px !important;
        }
        .dataTables_length select {
            height: 36px !important;
            width: 75px !important;
            padding: 4px 8px !important;
            background-image: none !important;
            -webkit-appearance: auto !important;
            appearance: auto !important;
        }
        .dataTables_filter input {
            height: 36px !important;
            padding: 4px 10px !important;
            border-radius: 6px !important;
            border: 1px solid #ced4da !important;
        }
        .filter-bar { display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; margin-bottom:16px; }
        .filter-bar > div { display:flex; flex-direction:column; }
        .filter-bar label { font-size:13px; font-weight:600; margin-bottom:4px; }

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
@endsection

@section('page-title') Late Time @endsection

@section('body')
<body data-sidebar="dark">
@endsection

@section('content')
@include('includes.flash')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <div class="filter-bar">
                        <div>
                            <label>Bulan</label>
                            <select id="filterMonth" class="form-control form-control-lg">
                                <option value="">Semua Bulan</option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div>
                            <label>Tahun</label>
                            <select id="filterYear" class="form-control form-control-lg">
                                <option value="">Semua Tahun</option>
                                @for($y = 2024; $y <= 2027; $y++)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label>Dari Tanggal</label>
                            <input type="date" id="filterDateFrom" class="form-control form-control-lg">
                        </div>
                        <div>
                            <label>Sampai Tanggal</label>
                            <input type="date" id="filterDateTo" class="form-control form-control-lg">
                        </div>
                        <div style="padding-top:22px;">
                            <button id="btnReset" class="btn btn-secondary" style="height:38px;font-size:15px;padding:0 20px;">Reset</button>
                        </div>
                    </div>

                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="latetime-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Late Duration</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
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
<script>
$(document).ready(function() {

            // Skeleton loading
            function showSkeleton() {
                var rows = '';
                for (var i = 0; i < 5; i++) {
                    rows += '<tr class="skeleton-row">';
                    for (var j = 0; j < 6; j++) {
                        rows += '<td><div class="skeleton-cell"></div></td>';
                    }
                    rows += '</tr>';
                }
                $('#latetime-table tbody').html(rows);
            }

    var table = $('#latetime-table').DataTable({
        processing: false,
        preDrawCallback: function() { showSkeleton(); },
        serverSide: false,
        ajax: {
            url: '/latetime/data',
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
            { data: 'late_duration', orderable: false },
            { data: 'time_in' },
            { data: 'time_out' },
        ],
        order: [[0, 'desc']],
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="mdi mdi-content-copy mr-1"></i> Copy',
                className: 'btn btn-sm btn-secondary',
            },
            {
                extend: 'excel',
                text: '<i class="mdi mdi-file-excel mr-1"></i> Excel',
                className: 'btn btn-sm btn-success',
                title: 'Late Time Data',
            },
            {
                extend: 'pdf',
                text: '<i class="mdi mdi-file-pdf mr-1"></i> PDF',
                className: 'btn btn-sm btn-danger',
                title: 'Late Time Data',
                orientation: 'landscape',
                pageSize: 'A4',
            },
        ],
    });

    $('#filterMonth, #filterYear, #filterDateFrom, #filterDateTo').on('change', function() {
        table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
        $('#filterMonth, #filterYear, #filterDateFrom, #filterDateTo').val('');
        table.ajax.reload();
    });
});
</script>
@endsection