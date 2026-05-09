@extends('layouts.master')

@section('css')
<style>
    /* ── Filter bar ── */
    .filter-bar { background: var(--surface); border:1px solid var(--border2); border-radius:10px; padding:14px 18px; margin-bottom:14px; }
    .filter-bar label { font-size:10px; font-weight:600; color:var(--text3); margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:.5px; }
    .filter-bar select, .filter-bar input { font-size:13px; height:34px; padding:4px 10px; }
    .filter-actions { gap: 8px; flex-wrap: wrap; }
    .sheet-summary {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        min-height: 34px;
    }
    .summary-pill {
        display: inline-flex;
        align-items: baseline;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 8px;
        color: var(--text2);
        background: var(--surface2);
        font-size: 11px;
        border: 1px solid var(--border2);
    }
    .summary-pill strong {
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
    }

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

    /* ── Table (border-collapse:separate is REQUIRED for sticky to work) ── */
    .attendance-table-wrap {
        isolation: isolate;
    }
    .check-table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        font-size: 11.5px;
        width: max-content;
        min-width: 100%;
        table-layout: fixed;
    }
    .check-table th, .check-table td {
        white-space: nowrap;
        padding: 5px 6px !important;
        vertical-align: middle !important;
        text-align: center;
        border: 1px solid #ddd !important;
        background-color: #fff;
        box-sizing: border-box;
    }
    .check-table tbody td:nth-child(2) {
        color: var(--text);
        font-weight: 600;
    }
    .check-table tbody td:nth-child(3) {
        color: var(--text2);
        font-size: 11px;
    }
    .check-table tbody tr:hover td:not(.day-sunday):not(.day-saturday):not(.day-today) {
        background-color: #FAF9F6 !important;
    }
    .check-table tbody tr:hover td.day-sunday { background: #F7E7CB !important; }
    .check-table tbody tr:hover td.day-saturday { background: #DDECF8 !important; }
    .check-table tbody tr:hover td.day-today { background: #DFEED0 !important; }
    .check-table tbody tr:hover td:nth-child(1),
    .check-table tbody tr:hover td:nth-child(2),
    .check-table tbody tr:hover td:nth-child(3) {
        background: #FAF9F6 !important;
    }

    /* ═══ FROZEN COLUMNS (ID, Nama, Jabatan) ═══ */
    .check-table td:nth-child(1),
    .check-table td:nth-child(2),
    .check-table td:nth-child(3) {
        text-align: left;
        position: sticky !important;
        z-index: 400 !important;
        background-color: #fff !important;
        opacity: 1 !important;
        transform: translateZ(0); /* Force stacking context */
        /* Cover sub-pixel gaps at fractional zoom levels */
        box-shadow: 0 0 0 2px #fff;
    }
    /* Col 1: ID */
    .check-table th:nth-child(1),
    .check-table td:nth-child(1) { left: 0 !important; min-width: 42px; width: 42px; max-width: 42px; }
    /* Col 2: Nama */
    .check-table th:nth-child(2),
    .check-table td:nth-child(2) { left: 42px !important; min-width: 130px; width: 130px; max-width: 130px; }
    /* Col 3: Jabatan — thick right border as visual separator */
    .check-table th:nth-child(3),
    .check-table td:nth-child(3) {
        left: 172px !important; min-width: 72px; width: 72px; max-width: 72px;
        border-right: 2px solid #aaa !important;
    }
    .check-table th:nth-child(2),
    .check-table td:nth-child(2),
    .check-table th:nth-child(3),
    .check-table td:nth-child(3) {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ═══ FROZEN HEADER ROWS ═══ */
    .check-table thead th {
        background-color: #EDECEA !important;
        font-weight: 600;
        color: #555;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .3px;
        position: sticky !important;
        z-index: 500 !important;
        line-height: 1.2;
        background-clip: padding-box;
        transform: translateZ(0); /* Force stacking context */
        opacity: 1 !important;
        /* Cover sub-pixel gaps at fractional zoom levels */
        box-shadow: 0 0 0 2px #EDECEA;
    }
    /* Row 1 (Week groups): stick to top */
    .header-row-1 th {
        top: 0 !important;
        height: 34px;
        min-height: 34px;
    }
    /* Row 2 (Dates): stick below row 1 */
    .header-row-2 th {
        top: 34px !important;
        height: 44px;
        min-height: 44px;
        padding: 3px 4px !important;
    }
    .header-row-2 th:not(:nth-child(1)):not(:nth-child(2)):not(:nth-child(3)) {
        width: 64px;
        min-width: 64px;
        max-width: 64px;
    }
    .header-date {
        display: flex;
        min-height: 34px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
    }
    .header-date-day {
        font-size: 9px;
        font-weight: 400;
        line-height: 1;
    }
    .week-header-sep { border-left: 2px solid var(--blue) !important; }

    /* Corner cells: header + frozen column = highest z-index */
    .check-table thead th:nth-child(1),
    .check-table thead th:nth-child(2),
    .check-table thead th:nth-child(3) {
        z-index: 600 !important;
        background-color: #EDECEA !important;
        opacity: 1 !important;
        transform: translateZ(0);
        text-align: left;
        position: sticky !important;
    }

    /* Cell tanggal */
    .time-cell {
        cursor: pointer;
        width: 64px;
        min-width: 64px;
        max-width: 64px;
        height: 62px;
        transition: background 0.12s;
        user-select: none;
        padding: 4px 5px !important;
    }
    .time-cell:hover { background: var(--surface2) !important; }
    .time-cell:focus-visible {
        outline: 2px solid var(--accent);
        outline-offset: -3px;
    }
    .time-cell.status-complete { box-shadow: inset 0 -2px 0 rgba(59,109,17,0.55); }
    .time-cell.status-partial { box-shadow: inset 0 -2px 0 rgba(163,45,45,0.5); }
    .time-cell.status-empty { color: var(--text3); }
    .time-cell.is-dirty {
        outline: 2px solid rgba(133,79,11,0.45);
        outline-offset: -3px;
    }
    .time-cell.is-editing {
        outline: 2px solid var(--blue);
        outline-offset: -3px;
    }
    .time-cell.is-saving {
        position: relative;
        opacity: .72;
        pointer-events: none;
    }
    .time-cell.is-saving::after {
        content: '';
        position: absolute;
        top: 6px;
        right: 6px;
        width: 10px;
        height: 10px;
        border: 2px solid rgba(0,0,0,.15);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    .time-cell.is-saved {
        animation: cellSaved .85s ease-out;
    }
    @keyframes cellSaved {
        0% { box-shadow: inset 0 0 0 999px rgba(59,109,17,.14); }
        100% { box-shadow: inherit; }
    }
    .time-cell .t-in    { color: var(--green); font-weight: 600; font-size: 11px; display: block; line-height: 1.35; }
    .time-cell .t-out   { color: var(--red); font-weight: 600; font-size: 11px; display: block; line-height: 1.35; }
    .time-cell .t-empty { color: var(--text3); font-size: 13px; display: block; line-height: 46px; }
    .time-cell .t-divider { color: var(--border); font-size: 8px; display: block; line-height: 1; }

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
        overflow: hidden;
    }

    /* Modal */
    .attendance-edit-dialog { max-width: 340px; }
    .attendance-edit-modal {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(17,24,39,.24);
    }
    .modal-header-edit {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--accent);
        color: var(--accent-text);
        padding: 14px 16px;
        border: 0;
    }
    .edit-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .edit-date-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: rgba(255,255,255,.12);
        color: var(--accent-text);
        flex: 0 0 auto;
    }
    .edit-title-text { min-width: 0; }
    .edit-date-label {
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
        line-height: 1.35;
        font-weight: 700;
    }
    .edit-emp-label  {
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 11px;
        line-height: 1.35;
        opacity: .72;
    }
    .modal-header-edit .edit-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        margin: 0;
        padding: 0;
        border-radius: 8px;
        color: var(--accent-text);
        text-shadow: none;
        opacity: .8;
    }
    .modal-header-edit .edit-close:hover {
        background: rgba(255,255,255,.12);
        opacity: 1;
    }
    .time-field-label {
        display: flex;
        align-items: center;
        margin-bottom: 7px;
        font-size: 11px;
        font-weight: 700;
        color: var(--text2);
        text-transform: uppercase;
        letter-spacing: .35px;
    }
    .time-input-row {
        display: grid;
        grid-template-columns: 1fr 38px;
        gap: 8px;
        align-items: center;
    }
    .time-input-row .form-control {
        height: 38px;
        font-size: 14px;
    }
    .dot-in  { width:6px;height:6px;border-radius:50%;background:var(--green);display:inline-block;margin-right:4px; }
    .dot-out { width:6px;height:6px;border-radius:50%;background:var(--red);display:inline-block;margin-right:4px; }
    .btn-clear-time {
        width: 38px;
        height: 38px;
        padding: 0;
        border-radius: 8px;
    }
    .manual-save-note {
        padding: 8px 10px;
        border-radius: 8px;
        color: var(--text2);
        background: var(--surface2);
        font-size: 11px;
    }
    .save-feedback {
        margin-bottom: 12px;
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }
    .save-feedback.is-error {
        color: var(--red);
        background: rgba(163,45,45,.08);
    }
    .empty-state-row td {
        height: 120px;
        color: var(--text2);
        background: var(--surface) !important;
        text-align: center !important;
    }
    .empty-state-title {
        display: block;
        color: var(--text);
        font-weight: 600;
        margin-bottom: 4px;
    }
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

    $weekendDays = collect($dates)->filter(fn($dateObj) => in_array($dateObj->dayOfWeek, [0, 6], true))->count();
    $workDays = count($dates) - $weekendDays;
@endphp

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
            <div class="sheet-summary mr-3 mb-2">
                <span class="summary-pill"><strong>{{ $filteredEmployees->count() }}</strong> karyawan</span>
                <span class="summary-pill"><strong>{{ count($dates) }}</strong> hari</span>
                <span class="summary-pill"><strong>{{ $workDays }}</strong> hari kerja</span>
            </div>
            {{-- Legend --}}
            <div class="ml-auto d-flex align-items-center mb-2 filter-actions">
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
                <div class="table-responsive attendance-table-wrap" style="max-height: 74vh; overflow: auto;">
                    <table class="table table-bordered table-sm check-table mb-0">
                        <colgroup>
                            <col style="width:42px;">
                            <col style="width:130px;">
                            <col style="width:72px;">
                            @foreach($dates as $dateObj)
                                <col style="width:64px;">
                            @endforeach
                        </colgroup>
                        <thead>
                            {{-- Baris 1: Week group --}}
                            <tr class="header-row-1">
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                @foreach($weeks as $wIdx => $weekDates)
                                    @php
                                        $wStart = $weekDates[0]->format('d');
                                        $wEnd   = end($weekDates)->format('d M');
                                    @endphp
                                    <th colspan="{{ count($weekDates) }}"
                                        class="week-group-th {{ $wIdx > 0 ? 'week-header-sep' : '' }}">
                                        Minggu {{ $wIdx+1 }} &nbsp;
                                        <span style="font-weight:400;opacity:0.7;">{{ $wStart }}–{{ $wEnd }}</span>
                                    </th>
                                @endforeach
                            </tr>
                            {{-- Baris 2: Tanggal --}}
                            <tr class="header-row-2">
                                <th></th>
                                <th></th>
                                <th></th>
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
                                            <span class="header-date">
                                                <span>{{ $dateObj->format('d') }}</span>
                                                <span class="header-date-day">{{ $dateObj->locale('id')->isoFormat('ddd') }}</span>
                                            </span>
                                        </th>
                                    @endforeach
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($filteredEmployees as $employee)
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
                                                $statusCls = $inVal && $outVal ? 'status-complete' : (($inVal || $outVal) ? 'status-partial' : 'status-empty');
                                            @endphp
                                            <td class="time-cell {{ $statusCls }} {{ $cls }} {{ $sep }}"
                                                data-date="{{ $dateStr }}"
                                                data-emp="{{ $employee->id }}"
                                                data-name="{{ $employee->name }}"
                                                data-day="{{ $dayLabel }}"
                                                data-in="{{ $inVal }}"
                                                data-out="{{ $outVal }}"
                                                data-original-in="{{ $inVal }}"
                                                data-original-out="{{ $outVal }}"
                                                role="button"
                                                tabindex="0"
                                                aria-label="Edit absensi {{ $employee->name }} pada {{ $dayLabel }}"
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
                            @empty
                                <tr class="empty-state-row">
                                    <td colspan="{{ 3 + count($dates) }}">
                                        <span class="empty-state-title">Tidak ada karyawan</span>
                                        Data karyawan tidak ditemukan untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="modalDayLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm attendance-edit-dialog" role="document">
        <div class="modal-content attendance-edit-modal">
            <div class="modal-header modal-header-edit">
                <div class="edit-title-wrap">
                    <span class="edit-date-icon"><i class="mdi mdi-calendar"></i></span>
                    <div class="edit-title-text">
                        <div class="edit-date-label" id="modalDayLabel">-</div>
                        <div class="edit-emp-label"  id="modalEmpLabel">-</div>
                    </div>
                </div>
                <button type="button" class="close edit-close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pb-2 pt-3">
                <div id="modalSaveFeedback" class="save-feedback is-error d-none"></div>
                <div class="mb-3">
                    <label class="time-field-label" for="modalIn"><span class="dot-in"></span> Scan In</label>
                    <div class="time-input-row">
                        <input type="time" id="modalIn" class="form-control" step="60">
                        <button type="button" class="btn btn-outline-secondary btn-clear-time" aria-label="Kosongkan scan in" title="Kosongkan scan in" onclick="clearTimeInput('modalIn')">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="time-field-label" for="modalOut"><span class="dot-out"></span> Scan Out</label>
                    <div class="time-input-row">
                        <input type="time" id="modalOut" class="form-control" step="60">
                        <button type="button" class="btn btn-outline-secondary btn-clear-time" aria-label="Kosongkan scan out" title="Kosongkan scan out" onclick="clearTimeInput('modalOut')">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                </div>
                <div class="manual-save-note">Kosongkan Scan In dan Scan Out untuk menghapus data hari ini.</div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveCell">
                    <i class="mdi mdi-content-save mr-1"></i><span class="save-cell-label">Simpan</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
// ── Force opaque backgrounds on frozen columns & headers (bypasses CSS specificity wars) ──
document.addEventListener('DOMContentLoaded', function() {
    var table = document.querySelector('.check-table');
    if (!table) return;

    // Force body cell backgrounds for frozen columns (col 1, 2, 3)
    table.querySelectorAll('tbody td:nth-child(1), tbody td:nth-child(2), tbody td:nth-child(3)').forEach(function(td) {
        td.style.setProperty('background-color', '#ffffff', 'important');
    });

    // Force header cell backgrounds
    table.querySelectorAll('thead th').forEach(function(th) {
        th.style.setProperty('background-color', '#EDECEA', 'important');
    });
});

let activeCell = null;

function openEditModal(cell) {
    if (activeCell) activeCell.classList.remove('is-editing');
    activeCell = cell;
    activeCell.classList.add('is-editing');
    $('#modalDayLabel').text(cell.dataset.day);
    $('#modalEmpLabel').text(cell.dataset.name);
    $('#modalIn').val(normalizeTimeForInput(cell.dataset.in || ''));
    $('#modalOut').val(normalizeTimeForInput(cell.dataset.out || ''));
    clearModalFeedback();
    setModalSaving(false);
    $('#editModal').modal('show');
    setTimeout(() => $('#modalIn').focus(), 400);
}

$('#btnSaveCell').on('click', function() {
    if (!activeCell) return;
    const cell   = activeCell;
    const date   = cell.dataset.date;
    const empId  = cell.dataset.emp;
    const inVal  = $('#modalIn').val();
    const outVal = $('#modalOut').val();

    setModalSaving(true);
    clearModalFeedback();
    cell.classList.add('is-saving');

    $.ajax({
        url: '{{ route('check_single_store') }}',
        type: 'POST',
        dataType: 'json',
        data: {
            _token: '{{ csrf_token() }}',
            date: date,
            emp_id: empId,
            time_in: inVal,
            time_out: outVal
        }
    }).done(function(response) {
        const savedIn  = normalizeTimeForDisplay(response.time_in !== undefined ? response.time_in : inVal);
        const savedOut = normalizeTimeForDisplay(response.time_out !== undefined ? response.time_out : outVal);

        cell.dataset.in = savedIn;
        cell.dataset.out = savedOut;
        cell.dataset.originalIn = savedIn;
        cell.dataset.originalOut = savedOut;
        updateCellDisplay(cell, savedIn, savedOut);
        $('#editModal').modal('hide');

        cell.classList.add('is-saved');
        setTimeout(() => cell.classList.remove('is-saved'), 900);
    }).fail(function(xhr) {
        const message = xhr.responseJSON && xhr.responseJSON.message
            ? xhr.responseJSON.message
            : 'Data belum tersimpan. Coba lagi.';
        showModalFeedback(message);
    }).always(function() {
        cell.classList.remove('is-saving');
        setModalSaving(false);
    });
});

function updateCellDisplay(cell, inVal, outVal) {
    if (inVal || outVal) {
        cell.innerHTML = `
            <span class="t-in">${inVal || '--:--'}</span>
            <span class="t-divider">--</span>
            <span class="t-out">${outVal || '--:--'}</span>`;
    } else {
        cell.innerHTML = '<span class="t-empty">.</span>';
    }
    applyCellStatus(cell, inVal, outVal);
}

function applyCellStatus(cell, inVal, outVal) {
    cell.classList.remove('status-complete', 'status-partial', 'status-empty');
    if (inVal && outVal) {
        cell.classList.add('status-complete');
    } else if (inVal || outVal) {
        cell.classList.add('status-partial');
    } else {
        cell.classList.add('status-empty');
    }
}

function normalizeTimeForInput(value) {
    return normalizeTimeForDisplay(value);
}

function normalizeTimeForDisplay(value) {
    if (!value) return '';
    const text = String(value);
    const match = text.match(/^(\d{2}:\d{2})/);
    return match ? match[1] : text;
}

function clearTimeInput(id) {
    $('#' + id).val('').focus();
}

function clearModalFeedback() {
    $('#modalSaveFeedback').addClass('d-none').text('');
}

function showModalFeedback(message) {
    $('#modalSaveFeedback').removeClass('d-none').text(message);
}

function setModalSaving(isSaving) {
    const btn = $('#btnSaveCell');
    btn.prop('disabled', isSaving);
    btn.find('.save-cell-label').text(isSaving ? 'Menyimpan...' : 'Simpan');
    $('#modalIn, #modalOut, .btn-clear-time').prop('disabled', isSaving);
}

// Bind keyboard edit on cells
document.querySelectorAll('.time-cell').forEach(cell => {
    cell.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openEditModal(cell);
        }
    });
});

// Enter di modal → simpan
$('#editModal').on('keydown', event => {
    if (event.key === 'Enter' && !$('#btnSaveCell').prop('disabled')) {
        event.preventDefault();
        $('#btnSaveCell').click();
    }
});
$('#editModal').on('hidden.bs.modal', () => {
    if (activeCell) activeCell.classList.remove('is-editing');
    clearModalFeedback();
});

// Auto-filter: reload on any dropdown change with loading overlay
document.querySelectorAll('.auto-filter').forEach(el => {
    el.dataset.currentValue = el.value;
    el.addEventListener('change', () => {
        const overlay = document.getElementById('tableLoadingOverlay');
        const loadingText = overlay ? overlay.querySelector('.table-loading-text') : null;
        if (loadingText) loadingText.textContent = 'Memuat data...';
        if (overlay) overlay.style.display = 'flex';
        const month = document.getElementById('filterMonth').value;
        const year  = document.getElementById('filterYear').value;
        const emp   = document.getElementById('filterEmp').value;
        window.location.href = `{{ route('check') }}?month=${month}&year=${year}&emp=${emp}`;
    });
});
</script>
@endsection
