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
                                        <th>{{ __('app.action') }}</th>
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

    <div class="modal fade" id="sheetReportEditModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Absensi</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editEmployeeId">
                    <div class="form-group">
                        <label>Karyawan</label>
                        <input type="text" id="editEmployeeName" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" id="editDate" class="form-control" readonly>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Jam Masuk</label>
                            <input type="time" id="editTimeIn" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Jam Keluar</label>
                            <input type="time" id="editTimeOut" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Izin/Cuti</label>
                        <select id="editReason" class="form-control">
                            <option value="">Tidak ada</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                            <option value="cuti">Cuti</option>
                            <option value="dinas">Dinas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea id="editNote" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveSheetReportRow">Simpan</button>
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
        processing: true,
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
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function() {
                    return '<button type="button" class="btn btn-sm btn-primary btn-edit-sheet-row"><i class="mdi mdi-pencil"></i> Edit</button>';
                }
            },
        ],
        columnDefs: [
            { targets: 0, width: '78px', className: 'text-center text-nowrap' },
            { targets: 4, width: '100px', className: 'text-center text-nowrap' },
            { targets: [5, 6, 7], width: '82px', className: 'text-center text-nowrap' },
            { targets: 8, width: '86px', className: 'text-center text-nowrap' },
            { targets: [9, 10, 11], width: '70px', className: 'text-center text-nowrap' },
            { targets: 12, width: '92px', className: 'text-center text-nowrap' },
            { targets: 13, width: '78px', className: 'text-center text-nowrap' },
        ],
        autoWidth: false,
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

    $('#sheet-report-table').on('click', '.btn-edit-sheet-row', function() {
        var row = table.row($(this).closest('tr')).data();
        if (!row) return;

        $('#editEmployeeId').val(row.employee_id);
        $('#editEmployeeName').val(row.emp_id + ' - ' + row.name);
        $('#editDate').val(row.tanggal);
        $('#editTimeIn').val(row.scan_1 && row.scan_1 !== '-' ? row.scan_1.substring(0, 5) : '');
        $('#editTimeOut').val(row.scan_2 && row.scan_2 !== '-' ? row.scan_2.substring(0, 5) : '');
        $('#editReason').val(row.izin_cuti && row.izin_cuti !== '-' ? row.izin_cuti.toLowerCase() : '');
        $('#editNote').val('');
        $('#sheetReportEditModal').modal('show');
    });

    $('#btnSaveSheetReportRow').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: '{{ route("sheet-report.update-row") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                employee_id: $('#editEmployeeId').val(),
                date: $('#editDate').val(),
                time_in: $('#editTimeIn').val(),
                time_out: $('#editTimeOut').val(),
                reason: $('#editReason').val(),
                note: $('#editNote').val()
            },
            success: function(res) {
                $('#sheetReportEditModal').modal('hide');
                table.ajax.reload(null, false);
                if (window.toastr) toastr.success(res.message || 'Data berhasil disimpan.');
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data.';
                if (window.toastr) toastr.error(msg); else alert(msg);
            },
            complete: function() {
                btn.prop('disabled', false).text('Simpan');
            }
        });
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
