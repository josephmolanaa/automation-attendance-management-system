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
        <h4 class="page-title text-left">Sheet Report</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Sheet Report</a></li>
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
                                @foreach(range(date('Y'), 2024) as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>&nbsp;</label>
                            <button id="btnReset" class="btn btn-secondary d-block">Reset</button>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button id="btnExport" class="btn btn-success d-block">
                                <i class="mdi mdi-file-excel mr-1"></i> Export Excel
                            </button>
                        </div>
                    </div>

                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="sheet-report-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:13px;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Hari</th>
                                        <th>Tanggal</th>
                                        <th>Scan 1</th>
                                        <th>Scan 2</th>
                                        <th>Scan 3</th>
                                        <th>Normal</th>
                                        <th>Double</th>
                                        <th>Minggu</th>
                                        <th>Izin/Cuti</th>
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
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
        buttons: [...amsExportButtons('Sheet Report')],
        language: {
            emptyTable: 'Tidak ada data yang tersedia',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 data',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            paginate: { next: 'Selanjutnya', previous: 'Sebelumnya' }
        },
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