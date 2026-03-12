@extends('layouts.master')

@section('css')
    <style>
        /* Hide Focus button from RWD plugin */
        .btn-focus-column, [id*="focus"], button[id*="focus"] { display: none !important; }

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
        .dataTables_info,
        .dataTables_paginate {
            font-size: 14px !important;
            padding-top: 10px !important;
        }
        .dataTables_paginate .paginate_button { font-size: 14px !important; }
        .dt-buttons { display: flex !important; align-items: center !important; gap: 6px !important; }
        .dt-buttons .btn { height: 38px !important; font-size: 14px !important; display: flex !important; align-items: center !important; }

        /* Sticky header */
        #attendance-table thead th {
            position: sticky !important;
            top: 0 !important;
            z-index: 10 !important;
            background: #f8f9fa !important;
            box-shadow: 0 2px 2px -1px rgba(0,0,0,0.15) !important;
        }
    </style>
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Attendance</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Attendance</a></li>
        </ol>
    </div>
@endsection

@section('button')
    <a href="attendance/assign" class="btn btn-primary btn-sm btn-flat"><i class="mdi mdi-plus mr-2"></i>Add New</a>
@endsection

@section('content')
@include('includes.flash')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    {{-- Filter Bar + Holiday Manager --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
                    <div class="d-flex flex-wrap" style="gap:10px; align-items:flex-end">
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
                    <div>
                        <label>&nbsp;</label>
                        <button class="btn btn-warning d-block" data-toggle="modal" data-target="#holidayManagerModal" style="height:38px;font-size:14px;">
                            <i class="mdi mdi-calendar-edit mr-1"></i> Holiday Manager
                        </button>
                    </div>
                    </div>

                    <div class="table-responsive mb-0">
                        <table id="attendance-table" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th style="min-width:120px">Employee ID</th>
                                    <th style="min-width:180px">Name</th>
                                    <th style="min-width:140px">Shift</th>
                                    <th style="min-width:100px">Status</th>
                                    <th style="min-width:110px">Date</th>
                                    <th style="min-width:100px">Time In</th>
                                    <th style="min-width:100px">Time Out</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')

    {{-- ===== MODAL HOLIDAY MANAGER ===== --}}
    <div class="modal fade" id="holidayManagerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-calendar-edit mr-2"></i>Holiday Manager</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-sm btn-outline-secondary" id="calPrevMonth">&#8249; Prev</button>
                        <h6 class="mb-0" id="calTitle"></h6>
                        <button class="btn btn-sm btn-outline-secondary" id="calNextMonth">Next &#8250;</button>
                    </div>
                    <div id="calendarGrid" class="mb-3">
                        <div class="row text-center font-weight-bold mb-1">
                            <div class="col">Min</div><div class="col">Sen</div><div class="col">Sel</div>
                            <div class="col">Rab</div><div class="col">Kam</div><div class="col">Jum</div>
                            <div class="col">Sab</div>
                        </div>
                        <div id="calDays"></div>
                    </div>
                    <div class="d-flex flex-wrap mb-3" style="gap:8px">
                        <span><span class="badge" style="background:#e74c3c">&nbsp;&nbsp;</span> Tanggal Merah (API)</span>
                        <span><span class="badge" style="background:#f39c12">&nbsp;&nbsp;</span> Override Admin</span>
                        <span><span class="badge" style="background:#3498db">&nbsp;&nbsp;</span> Weekday</span>
                        <span><span class="badge" style="background:#2ecc71">&nbsp;&nbsp;</span> Sabtu</span>
                        <span><span class="badge" style="background:#95a5a6">&nbsp;&nbsp;</span> Minggu</span>
                    </div>
                    <div id="overrideForm" class="card card-body bg-light d-none">
                        <h6 id="overrideFormTitle" class="mb-3"></h6>
                        <div class="form-group">
                            <label>Tipe Hari</label>
                            <select id="overrideType" class="form-control">
                                <option value="weekday">Weekday (Masuk Kerja Biasa)</option>
                                <option value="saturday">Sabtu</option>
                                <option value="holiday">Holiday / Libur</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Shift Spesifik <small class="text-muted">(opsional)</small></label>
                            <select id="overrideSchedule" class="form-control">
                                <option value="">-- Auto detect dari jam scan --</option>
                                @foreach(\App\Models\Schedule::all() as $s)
                                    <option value="{{ $s->id }}">{{ $s->slug }} ({{ $s->time_in }} - {{ $s->time_out }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Catatan</label>
                            <input type="text" id="overrideNote" class="form-control" placeholder="contoh: Masuk kerja meski libur nasional">
                        </div>
                        <input type="hidden" id="overrideDate">
                        <div class="d-flex" style="gap:8px">
                            <button class="btn btn-primary btn-sm" id="btnSaveOverride">
                                <i class="mdi mdi-content-save mr-1"></i> Simpan Override
                            </button>
                            <button class="btn btn-danger btn-sm d-none" id="btnDeleteOverride">
                                <i class="mdi mdi-delete mr-1"></i> Hapus Override
                            </button>
                            <button class="btn btn-secondary btn-sm" id="btnCancelOverride">Batal</button>
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
            var table = $('#attendance-table').DataTable({
                destroy: true,
                processing: false,
                serverSide: false,
                ajax: {
                    url: '/attendance/data',
                    type: 'GET',
                    data: function(d) {
                        d.bulan  = $('#filterMonth').val();
                        d.tahun  = $('#filterYear').val();
                        d.dari   = $('#filterDateFrom').val();
                        d.sampai = $('#filterDateTo').val();
                    }
                },
                columns: [
                    { data: 'emp_id' },
                    { data: 'name' },
                    { data: 'shift', orderable: false },
                    { data: 'status', orderable: false },
                    { data: 'date' },
                    { data: 'time_in' },
                    { data: 'time_out' },
                ],
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"d-flex justify-content-between align-items-center mb-2"lBf>rtip',
                buttons: [
                    { extend: 'copy',  text: '<i class="mdi mdi-content-copy mr-1"></i> Copy',  className: 'btn btn-sm btn-secondary' },
                    { extend: 'excel', text: '<i class="mdi mdi-file-excel mr-1"></i> Excel',   className: 'btn btn-sm btn-success', title: 'Attendance Data' },
                    { extend: 'pdf',   text: '<i class="mdi mdi-file-pdf mr-1"></i> PDF',       className: 'btn btn-sm btn-danger',  title: 'Attendance Data', orientation: 'landscape', pageSize: 'A4' },
                ],
                order: [[4, 'desc']],
                language: {
                    zeroRecords: 'Loading...',
                    emptyTable: 'Tidak ada data tersedia',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 data',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    paginate: { next: 'Selanjutnya', previous: 'Sebelumnya' }
                },
            });

            window.attendanceTable = table;

            $('#filterMonth, #filterYear, #filterDateFrom, #filterDateTo').on('change', function() {
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $('#filterMonth').val('');
                $('#filterYear').val('');
                $('#filterDateFrom').val('');
                $('#filterDateTo').val('');
                table.ajax.reload();
            });
        });
    </script>

    <script>
    var now      = new Date();
    var calYear  = now.getFullYear();
    var calMonth = now.getMonth() + 1;
    var calHolidays  = [];
    var calOverrides = {};

    function loadCalendar(year, month) {
        calYear  = year;
        calMonth = month;
        var monthNames = ['Januari','Februari','Maret','April','Mei','Juni',
                          'Juli','Agustus','September','Oktober','November','Desember'];
        $('#calTitle').text(monthNames[month - 1] + ' ' + year);
        $.get('/holiday-overrides/month', { year: year, month: month }, function(res) {
            calHolidays  = res.holidays  || [];
            calOverrides = res.overrides || {};
            renderCalendar(year, month);
        });
    }

    function renderCalendar(year, month) {
        var firstDay = new Date(year, month - 1, 1).getDay();
        var daysInMonth = new Date(year, month, 0).getDate();
        var html = '<div class="row text-center">';
        var day = 1;
        for (var cell = 0; cell < 42; cell++) {
            if (cell % 7 === 0 && cell > 0) html += '</div><div class="row text-center">';
            if (cell === 0) html += '';
            if (cell < firstDay || day > daysInMonth) {
                html += '<div class="col p-1"></div>';
            } else {
                var mm      = String(month).padStart(2, '0');
                var dd      = String(day).padStart(2, '0');
                var dateStr = year + '-' + mm + '-' + dd;
                var dow     = new Date(year, month - 1, day).getDay();
                var bg = '#fff', color = '#333', title = '';
                if (calOverrides[dateStr]) {
                    bg = '#f39c12'; color = '#fff'; title = 'Override: ' + calOverrides[dateStr].override_type;
                } else if (calHolidays.includes(dateStr)) {
                    bg = '#e74c3c'; color = '#fff'; title = 'Tanggal Merah';
                } else if (dow === 0) {
                    bg = '#95a5a6'; color = '#fff'; title = 'Minggu';
                } else if (dow === 6) {
                    bg = '#2ecc71'; color = '#fff'; title = 'Sabtu';
                } else {
                    bg = '#3498db'; color = '#fff'; title = 'Weekday';
                }
                html += '<div class="col p-1"><div class="rounded text-center py-1 px-0 cal-day" '
                      + 'data-date="' + dateStr + '" title="' + title + '" '
                      + 'style="background:' + bg + ';color:' + color + ';cursor:pointer;font-size:12px">'
                      + day + '</div></div>';
                day++;
            }
        }
        html += '</div>';
        $('#calDays').html(html);
        $('#calDays').off('click', '.cal-day').on('click', '.cal-day', function() {
            openOverrideForm($(this).data('date'));
        });
    }

    function openOverrideForm(date) {
        $.get('/holiday-overrides/date-info', { date: date }, function(res) {
            $('#overrideDate').val(date);
            $('#overrideFormTitle').html(
                '<i class="mdi mdi-calendar mr-1"></i> ' + res.nama_hari + ', ' + date
                + (res.is_api_holiday ? ' <span class="badge badge-danger ml-1">Tanggal Merah</span>' : '')
            );
            if (res.override) {
                $('#overrideType').val(res.override.override_type);
                $('#overrideSchedule').val(res.override.schedule_id || '');
                $('#overrideNote').val(res.override.note || '');
                $('#btnDeleteOverride').removeClass('d-none');
            } else {
                $('#overrideType').val(res.original_type);
                $('#overrideSchedule').val('');
                $('#overrideNote').val('');
                $('#btnDeleteOverride').addClass('d-none');
            }
            $('#overrideForm').removeClass('d-none');
        });
    }

    $('#calPrevMonth').on('click', function() {
        calMonth--;
        if (calMonth < 1) { calMonth = 12; calYear--; }
        loadCalendar(calYear, calMonth);
    });
    $('#calNextMonth').on('click', function() {
        calMonth++;
        if (calMonth > 12) { calMonth = 1; calYear++; }
        loadCalendar(calYear, calMonth);
    });
    $('#btnSaveOverride').on('click', function() {
        $.post('/holiday-overrides', {
            _token:        '{{ csrf_token() }}',
            date:          $('#overrideDate').val(),
            override_type: $('#overrideType').val(),
            schedule_id:   $('#overrideSchedule').val() || null,
            note:          $('#overrideNote').val(),
        }, function(res) {
            if (res.success) {
                swal({ title: 'Berhasil!', text: res.message, icon: 'success', button: true, timer: 2500 })
                    .then(function() {
                        loadCalendar(calYear, calMonth);
                        if (window.attendanceTable) window.attendanceTable.ajax.reload();
                    });
                $('#overrideForm').addClass('d-none');
            }
        });
    });
    $('#btnDeleteOverride').on('click', function() {
        $.ajax({
            url: '/holiday-overrides', type: 'DELETE',
            data: { _token: '{{ csrf_token() }}', date: $('#overrideDate').val() },
            success: function(res) {
                if (res.success) {
                    loadCalendar(calYear, calMonth);
                    if (window.attendanceTable) window.attendanceTable.ajax.reload();
                    $('#overrideForm').addClass('d-none');
                }
            }
        });
    });
    $('#btnCancelOverride').on('click', function() {
        $('#overrideForm').addClass('d-none');
    });
    $('#holidayManagerModal').on('show.bs.modal', function() {
        var now = new Date();
        calYear  = now.getFullYear();
        calMonth = now.getMonth() + 1;
        loadCalendar(calYear, calMonth);
    });
    </script>
@endsection