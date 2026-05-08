@extends('layouts.master')

@section('css')
<style>
    /* ── Filter bar ── */
    .filter-bar { background: var(--surface); border:1px solid var(--border2); border-radius:10px; padding:14px 18px; margin-bottom:14px; }
    .filter-bar label { font-size:10px; font-weight:600; color:var(--text3); margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:.5px; }
    .filter-bar select, .filter-bar input { font-size:13px; height:34px; padding:4px 10px; }

    /* ── Loading Overlay ── */
    .table-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(247,246,243,0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
        border-radius: 10px;
        backdrop-filter: blur(2px);
    }
    .table-loading-text {
        font-size: 13px;
        font-weight: 500;
        color: var(--text2);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .table-loading-text::before {
        content: '';
        width: 16px; height: 16px;
        border: 2px solid var(--border);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Table ── */
    .check-table {
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11.5px;
    }
    .check-table th, .check-table td {
        white-space: nowrap;
        padding: 4px 3px !important;
        vertical-align: middle !important;
        text-align: center;
        border: 1px solid var(--border2);
    }

    /* ═══ FROZEN COLUMNS (ID, Nama, Jabatan) ═══ */
    .check-table th:nth-child(1),
    .check-table th:nth-child(2),
    .check-table th:nth-child(3),
    .check-table td:nth-child(1),
    .check-table td:nth-child(2),
    .check-table td:nth-child(3) {
        text-align: left;
        position: sticky !important;
        z-index: 10 !important;
        background: var(--surface) !important;
        border-right: 1px solid var(--border) !important;
    }
    .check-table th:nth-child(1), .check-table td:nth-child(1) { left: 0;    min-width: 36px; }
    .check-table th:nth-child(2), .check-table td:nth-child(2) { left: 40px; min-width: 140px; }
    .check-table th:nth-child(3), .check-table td:nth-child(3) { left: 180px; min-width: 65px; box-shadow: 3px 0 6px -2px rgba(0,0,0,0.06); }

    /* ═══ FROZEN HEADER ROWS ═══ */
    .check-table thead th {
        background: var(--surface2) !important;
        font-weight: 600;
        color: var(--text2);
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .3px;
        position: sticky !important;
        top: 0;
        z-index: 20 !important;
    }
    /* Corner cells (header + frozen column) need highest z-index */
    .check-table thead th:nth-child(1),
    .check-table thead th:nth-child(2),
    .check-table thead th:nth-child(3) {
        z-index: 30 !important;
        background: var(--surface2) !important;
    }
    /* Second header row (dates) — sticky below first row */
    .check-table thead tr:nth-child(2) th {
        top: 28px; /* height of first header row */
    }

    /* Week separator */
    .week-sep { border-left: 2px solid var(--blue) !important; }
    .week-header-sep { border-left: 2px solid var(--blue) !important; }

    /* Cell tanggal */
    .time-cell {
        cursor: pointer;
        min-width: 64px;
        transition: background 0.12s;
        user-select: none;
    }
    .time-cell:hover { background: var(--surface2) !important; }
    .time-cell .t-in    { color: var(--green); font-weight: 600; font-size: 11px; display: block; line-height: 1.6; }
    .time-cell .t-out   { color: var(--red); font-weight: 600; font-size: 11px; display: block; line-height: 1.6; }
    .time-cell .t-empty { color: var(--text3); font-size: 13px; display: block; line-height: 2.4; }
    .time-cell .t-divider { color: var(--border); font-size: 8px; }

    /* Warna hari */
    .day-sunday   { background: var(--amber-bg) !important; }
    .day-saturday { background: var(--blue-bg) !important; }
    .day-today    { background: var(--green-bg) !important; }

    /* Week group header */
    .week-group-th {
        background: var(--surface2) !important;
        font-size: 10px;
        font-weight: 600;
        color: var(--text2);
        padding: 2px 4px !important;
        border-left: 2px solid var(--blue) !important;
    }

    /* Modal */
    .modal-header-edit { background: var(--accent); color: var(--accent-text); padding: 14px 18px; border-radius: 13px 13px 0 0; }
    .modal-header-edit .close { color: var(--accent-text); opacity: 0.7; }
    .edit-date-label { font-size: 13px; font-weight: 600; }
    .edit-emp-label  { font-size: 11px; opacity: 0.7; }
    .dot-in  { width:6px;height:6px;border-radius:50%;background:var(--green);display:inline-block;margin-right:4px; }
    .dot-out { width:6px;height:6px;border-radius:50%;background:var(--red);display:inline-block;margin-right:4px; }
    .btn-clear-time { font-size: 11px; padding: 2px 8px; }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Manual Attendance</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item">Manual Attendance</li>
    </ol>
</div>
@endsection

@section('button')
<button id="btnSaveAll" class="btn btn-success btn-sm">
    <i class="mdi mdi-content-save mr-1"></i> Simpan Semua
</button>
@endsection

@section('content')
@include('includes.flash')

@php
    $today       = today();
    $selMonth    = request('month', $today->month);
    $selYear     = request('year',  $today->year);
    $selEmp      = request('emp',   'all');

    $daysInMonth = \Carbon\Carbon::createFromDate($selYear, $selMonth, 1)->daysInMonth;
    $dates = [];
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $dates[] = \Carbon\Carbon::createFromDate($selYear, $selMonth, $i);
    }

    // Filter karyawan
    $allEmployees = $employees; // dari controller
    $filteredEmployees = $selEmp === 'all'
        ? $allEmployees
        : $allEmployees->filter(fn($e) => $e->id == $selEmp);

    // Load checks
    $monthStart = \Carbon\Carbon::createFromDate($selYear, $selMonth, 1)->startOfDay();
    $monthEnd   = \Carbon\Carbon::createFromDate($selYear, $selMonth, $daysInMonth)->endOfDay();

    $allChecks = \App\Models\Check::where(function($q) use ($monthStart, $monthEnd) {
        $q->whereBetween('attendance_time', [$monthStart, $monthEnd])
          ->orWhere(function($q2) use ($monthStart, $monthEnd) {
              $q2->whereNull('attendance_time')
                 ->whereBetween('leave_time', [$monthStart, $monthEnd]);
          })
          ->orWhereBetween('leave_time', [$monthStart, $monthEnd]);
    })->get()->groupBy('emp_id');

    $months = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
        5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];

    // Kelompokkan tanggal per minggu
    $weeks = [];
    $weekIdx = 0;
    foreach ($dates as $dateObj) {
        $dow = $dateObj->dayOfWeek; // 0=Minggu
        if ($dow === 1 && !empty($weeks)) $weekIdx++; // Mulai minggu baru di Senin
        $weeks[$weekIdx][] = $dateObj;
    }
@endphp

{{-- Hidden form --}}
<form id="checkForm" action="{{ route('check_store') }}" method="post">
    @csrf
    <div id="hiddenInputs"></div>
</form>

<div class="row">
    <div class="col-12">

        {{-- Filter Bar --}}
        <div class="filter-bar d-flex flex-wrap align-items-end">
            {{-- Bulan --}}
            <div class="mr-3 mb-2">
                <label>Bulan</label>
                <select id="filterMonth" class="form-control auto-filter">
                    @foreach($months as $m => $mName)
                        <option value="{{ $m }}" {{ $selMonth == $m ? 'selected' : '' }}>{{ $mName }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Tahun --}}
            <div class="mr-3 mb-2">
                <label>Tahun</label>
                <select id="filterYear" class="form-control auto-filter">
                    @for($y = 2024; $y <= 2027; $y++)
                        <option value="{{ $y }}" {{ $selYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            {{-- Karyawan --}}
            <div class="mr-3 mb-2">
                <label>Karyawan</label>
                <select id="filterEmp" class="form-control auto-filter" style="min-width:160px;">
                    <option value="all" {{ $selEmp === 'all' ? 'selected' : '' }}>Semua Karyawan</option>
                    @foreach($allEmployees as $emp)
                        <option value="{{ $emp->id }}" {{ $selEmp == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Legend --}}
            <div class="ml-auto d-flex align-items-center mb-2" style="gap:10px;flex-wrap:wrap;">
                <small><span class="dot-in"></span>In</small>
                <small><span class="dot-out"></span>Out</small>
                <small><span style="display:inline-block;width:10px;height:10px;background:var(--amber-bg);border:1px solid var(--border);border-radius:2px;margin-right:3px;"></span>Minggu</small>
                <small><span style="display:inline-block;width:10px;height:10px;background:var(--blue-bg);border:1px solid var(--border);border-radius:2px;margin-right:3px;"></span>Sabtu</small>
                <small><i class="mdi mdi-cursor-default-click"></i> Klik cell untuk edit</small>
            </div>
        </div>

        <div class="card" style="position:relative;">
            <div id="tableLoadingOverlay" class="table-loading-overlay" style="display:none;">
                <div class="table-loading-text">Memuat data...</div>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive" style="max-height: 74vh; overflow: auto;">
                    <table class="table table-bordered table-sm check-table mb-0">
                        <thead>
                            {{-- Baris 1: Week group --}}
                            <tr>
                                <th rowspan="2">ID</th>
                                <th rowspan="2">Nama</th>
                                <th rowspan="2">Jabatan</th>
                                @foreach($weeks as $wIdx => $weekDates)
                                    @php
                                        $wStart = $weekDates[0]->format('d');
                                        $wEnd   = end($weekDates)->format('d M');
                                    @endphp
                                    <th colspan="{{ count($weekDates) }}"
                                        class="week-group-th {{ $wIdx > 0 ? 'week-header-sep' : '' }}">
                                        Minggu {{ $wIdx+1 }} &nbsp;
                                        <span style="font-weight:400;opacity:0.8;">{{ $wStart }}–{{ $wEnd }}</span>
                                    </th>
                                @endforeach
                            </tr>
                            {{-- Baris 2: Tanggal --}}
                            <tr>
                                @php $firstOfWeek = true; @endphp
                                @foreach($weeks as $wIdx => $weekDates)
                                    @foreach($weekDates as $dIdx => $dateObj)
                                        @php
                                            $dow = $dateObj->dayOfWeek;
                                            $cls = '';
                                            if ($dateObj->isToday())   $cls = 'day-today';
                                            elseif ($dow === 0)        $cls = 'day-sunday';
                                            elseif ($dow === 6)        $cls = 'day-saturday';
                                            $sep = ($wIdx > 0 && $dIdx === 0) ? 'week-sep' : '';
                                        @endphp
                                        <th class="{{ $cls }} {{ $sep }}">
                                            {{ $dateObj->format('d') }}<br>
                                            <span style="font-weight:400;font-size:9px;">{{ $dateObj->locale('id')->isoFormat('ddd') }}</span>
                                        </th>
                                    @endforeach
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filteredEmployees as $employee)
                                @php
                                    $empChecks   = $allChecks->get($employee->id, collect());
                                    $checkByDate = $empChecks->keyBy(function($c) {
                                        $t = $c->attendance_time ?? $c->leave_time;
                                        return \Carbon\Carbon::parse($t)->format('Y-m-d');
                                    });
                                @endphp
                                <tr>
                                    <td>{{ $employee->id }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->position ?? '-' }}</td>

                                    @foreach($weeks as $wIdx => $weekDates)
                                        @foreach($weekDates as $dIdx => $dateObj)
                                            @php
                                                $dateStr = $dateObj->format('Y-m-d');
                                                $dow     = $dateObj->dayOfWeek;
                                                $cls     = '';
                                                if ($dateObj->isToday())   $cls = 'day-today';
                                                elseif ($dow === 0)        $cls = 'day-sunday';
                                                elseif ($dow === 6)        $cls = 'day-saturday';
                                                $sep = ($wIdx > 0 && $dIdx === 0) ? 'week-sep' : '';

                                                $check  = $checkByDate->get($dateStr);
                                                $inVal  = $check && $check->attendance_time
                                                    ? \Carbon\Carbon::parse($check->attendance_time)->format('H:i')
                                                    : '';
                                                $outVal = $check && $check->leave_time
                                                    ? \Carbon\Carbon::parse($check->leave_time)->format('H:i')
                                                    : '';
                                                $dayLabel = $dateObj->locale('id')->isoFormat('dddd, D MMM YYYY');
                                            @endphp
                                            <td class="time-cell {{ $cls }} {{ $sep }}"
                                                data-date="{{ $dateStr }}"
                                                data-emp="{{ $employee->id }}"
                                                data-name="{{ $employee->name }}"
                                                data-day="{{ $dayLabel }}"
                                                data-in="{{ $inVal }}"
                                                data-out="{{ $outVal }}"
                                                onclick="openEditModal(this)">
                                                @if($inVal || $outVal)
                                                    <span class="t-in">{{ $inVal ?: '--:--' }}</span>
                                                    <span class="t-divider">──</span>
                                                    <span class="t-out">{{ $outVal ?: '--:--' }}</span>
                                                @else
                                                    <span class="t-empty">·</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-edit">
                <div>
                    <div class="edit-date-label" id="modalDayLabel">-</div>
                    <div class="edit-emp-label"  id="modalEmpLabel">-</div>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body pb-2">
                <div class="mb-3">
                    <label style="font-size:12px;font-weight:600;"><span class="dot-in"></span> Scan In</label>
                    <div class="d-flex" style="gap:8px;">
                        <input type="time" id="modalIn" class="form-control" step="1">
                        <button type="button" class="btn btn-outline-secondary btn-clear-time" onclick="$('#modalIn').val('')">✕</button>
                    </div>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;"><span class="dot-out"></span> Scan Out</label>
                    <div class="d-flex" style="gap:8px;">
                        <input type="time" id="modalOut" class="form-control" step="1">
                        <button type="button" class="btn btn-outline-secondary btn-clear-time" onclick="$('#modalOut').val('')">✕</button>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">* Kosongkan keduanya untuk hapus data hari ini</small>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveCell">
                    <i class="mdi mdi-content-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let activeCell = null;

function openEditModal(cell) {
    activeCell = cell;
    $('#modalDayLabel').text(cell.dataset.day);
    $('#modalEmpLabel').text(cell.dataset.name);
    $('#modalIn').val(cell.dataset.in || '');
    $('#modalOut').val(cell.dataset.out || '');
    $('#editModal').modal('show');
    setTimeout(() => $('#modalIn').focus(), 400);
}

$('#btnSaveCell').on('click', function() {
    if (!activeCell) return;
    const date   = activeCell.dataset.date;
    const empId  = activeCell.dataset.emp;
    const inVal  = $('#modalIn').val();
    const outVal = $('#modalOut').val();

    activeCell.dataset.in  = inVal;
    activeCell.dataset.out = outVal;
    updateCellDisplay(activeCell, inVal, outVal);
    updateHiddenInput('time_in',  date, empId, inVal);
    updateHiddenInput('time_out', date, empId, outVal);
    $('#editModal').modal('hide');
});

function updateCellDisplay(cell, inVal, outVal) {
    if (inVal || outVal) {
        cell.innerHTML = `
            <span class="t-in">${inVal || '--:--'}</span>
            <span class="t-divider">──</span>
            <span class="t-out">${outVal || '--:--'}</span>`;
    } else {
        cell.innerHTML = '<span class="t-empty">·</span>';
    }
}

function updateHiddenInput(type, date, empId, value) {
    const name = `${type}[${date}][${empId}]`;
    let inp = document.querySelector(`#hiddenInputs input[name="${CSS.escape(name)}"]`);
    if (!inp) {
        inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = name;
        document.getElementById('hiddenInputs').appendChild(inp);
    }
    inp.value = value;
}

// Load nilai awal ke hidden inputs
document.querySelectorAll('.time-cell').forEach(cell => {
    const { date, emp } = cell.dataset;
    if (cell.dataset.in)  updateHiddenInput('time_in',  date, emp, cell.dataset.in);
    if (cell.dataset.out) updateHiddenInput('time_out', date, emp, cell.dataset.out);
});

// Enter di modal → simpan
$('#editModal').on('keydown', e => { if (e.key === 'Enter') $('#btnSaveCell').click(); });

// Tombol Simpan Semua
document.getElementById('btnSaveAll').addEventListener('click', () => {
    document.getElementById('checkForm').submit();
});

// Auto-filter: reload on any dropdown change with loading overlay
document.querySelectorAll('.auto-filter').forEach(el => {
    el.addEventListener('change', () => {
        const overlay = document.getElementById('tableLoadingOverlay');
        if (overlay) overlay.style.display = 'flex';
        const month = document.getElementById('filterMonth').value;
        const year  = document.getElementById('filterYear').value;
        const emp   = document.getElementById('filterEmp').value;
        window.location.href = `{{ route('check') }}?month=${month}&year=${year}&emp=${emp}`;
    });
});
</script>
@endsection