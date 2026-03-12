@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <style>
        .dataTables_length label,
        .dataTables_filter label,
        .dataTables_length select,
        .dataTables_filter input { font-size: 14px !important; }
        .dataTables_length select {
            height: 36px !important; width: 75px !important; padding: 4px 8px !important;
            background-image: none !important; -webkit-appearance: auto !important; appearance: auto !important;
        }
        .dataTables_filter input {
            height: 36px !important; padding: 4px 10px !important;
            border-radius: 6px !important; border: 1px solid #ced4da !important;
        }
        .dt-buttons { display: flex !important; align-items: center !important; gap: 6px !important; }
        .dt-buttons .btn { height: 38px !important; font-size: 14px !important; display: flex !important; align-items: center !important; }
    </style>
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



@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    {{-- Filter Bar --}}
                    <div class="d-flex flex-wrap" style="gap:10px; align-items:flex-end; margin-bottom:16px;">
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
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
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
                            <table id="overtime-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
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

@section('script-bottom')
<script>
$(function () {
    var table = $('#overtime-table').DataTable({
        destroy: true,
        processing: false,
        serverSide: false,
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
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lBf>rtip',
        buttons: [
            { extend: 'copy',  text: '<i class="mdi mdi-content-copy mr-1"></i> Copy',  className: 'btn btn-sm btn-secondary' },
            { extend: 'excel', text: '<i class="mdi mdi-file-excel mr-1"></i> Excel',   className: 'btn btn-sm btn-success', title: 'Overtime Data' },
            { extend: 'pdf',   text: '<i class="mdi mdi-file-pdf mr-1"></i> PDF',       className: 'btn btn-sm btn-danger',  title: 'Overtime Data', orientation: 'landscape', pageSize: 'A4' },
        ],
        order: [[0, 'desc']],
        language: {
            emptyTable: 'Tidak ada data tersedia',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 data',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            paginate: { next: 'Selanjutnya', previous: 'Sebelumnya' }
        },
    });

    $('#filterMonth, #filterYear, #filterDateFrom, #filterDateTo').on('change', function() {
        table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
        $('#filterMonth').val('');
        $('#filterYear').val('{{ date("Y") }}');
        $('#filterDateFrom, #filterDateTo').val('');
        table.ajax.reload();
    });
});
</script>
@endsection