@extends('layouts.master')

@section('css')
<style>
    .check-table th, .check-table td {
        white-space: nowrap;
        font-size: 12px;
        padding: 4px 6px !important;
        vertical-align: middle !important;
        text-align: center;
    }
    .check-table th:nth-child(1),
    .check-table th:nth-child(2),
    .check-table th:nth-child(3),
    .check-table td:nth-child(1),
    .check-table td:nth-child(2),
    .check-table td:nth-child(3) {
        text-align: left;
        position: sticky;
        background: #fff;
        z-index: 2;
    }
    .check-table th:nth-child(1), .check-table td:nth-child(1) { left: 0;   min-width: 40px; }
    .check-table th:nth-child(2), .check-table td:nth-child(2) { left: 50px; min-width: 160px; }
    .check-table th:nth-child(3), .check-table td:nth-child(3) { left: 210px; min-width: 80px; }
    .check-table thead th { background: #f8f9fa !important; }

    .time-input {
        width: 70px;
        font-size: 11px;
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 3px;
        text-align: center;
    }
    .time-input:focus { border-color: #80bdff; outline: none; }
    .cell-in  { color: #155724; font-weight: 600; font-size: 11px; }
    .cell-out { color: #721c24; font-weight: 600; font-size: 11px; }

    /* Warna hari */
    .day-sunday   { background: #fff3cd !important; }
    .day-saturday { background: #e8f4fd !important; }
    .day-holiday  { background: #f8d7da !important; }
    .day-today    { background: #d4edda !important; }
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
<button form="checkForm" type="submit" class="btn btn-success btn-sm">
    <i class="mdi mdi-content-save mr-1"></i> Simpan
</button>
@endsection

@section('content')
@include('includes.flash')

@php
    $today     = today();
    $daysInMonth = $today->daysInMonth;
    $dates     = [];
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $dates[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i);
    }

    // Pre-load semua checks bulan ini
    $allChecks = \App\Models\Check::whereYear('attendance_time', $today->year)
        ->whereMonth('attendance_time', $today->month)
        ->get()
        ->groupBy('emp_id');
@endphp

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-2">
                <form id="checkForm" action="{{ route('check_store') }}" method="post">
                    @csrf
                    <div class="table-responsive" style="max-height: 75vh; overflow: auto;">
                        <table class="table table-bordered table-sm check-table mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    @foreach ($dates as $dateObj)
                                        @php
                                            $dow = $dateObj->dayOfWeek;
                                            $cls = '';
                                            if ($dateObj->isToday()) $cls = 'day-today';
                                            elseif ($dow === 0) $cls = 'day-sunday';
                                            elseif ($dow === 6) $cls = 'day-saturday';
                                        @endphp
                                        <th class="{{ $cls }}" style="min-width:80px">
                                            {{ $dateObj->format('d') }}<br>
                                            <small>{{ $dateObj->locale('id')->isoFormat('ddd') }}</small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    @php
                                        $empChecks = $allChecks->get($employee->id, collect());
                                        // Group by date
                                        $checkByDate = $empChecks->keyBy(function($c) {
                                            return \Carbon\Carbon::parse($c->attendance_time)->format('Y-m-d');
                                        });
                                    @endphp
                                    <tr>
                                        <td>{{ $employee->id }}</td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $employee->position ?? '-' }}</td>

                                        @foreach ($dates as $dateObj)
                                            @php
                                                $dateStr = $dateObj->format('Y-m-d');
                                                $dow     = $dateObj->dayOfWeek;
                                                $cls     = '';
                                                if ($dateObj->isToday()) $cls = 'day-today';
                                                elseif ($dow === 0) $cls = 'day-sunday';
                                                elseif ($dow === 6) $cls = 'day-saturday';

                                                $check   = $checkByDate->get($dateStr);
                                                $inVal   = $check && $check->attendance_time
                                                    ? \Carbon\Carbon::parse($check->attendance_time)->format('H:i')
                                                    : '';
                                                $outVal  = $check && $check->leave_time
                                                    ? \Carbon\Carbon::parse($check->leave_time)->format('H:i')
                                                    : '';
                                            @endphp
                                            <td class="{{ $cls }}">
                                                <div>
                                                    <span class="cell-in">IN</span><br>
                                                    <input
                                                        type="time"
                                                        name="time_in[{{ $dateStr }}][{{ $employee->id }}]"
                                                        value="{{ $inVal }}"
                                                        class="time-input">
                                                </div>
                                                <div class="mt-1">
                                                    <span class="cell-out">OUT</span><br>
                                                    <input
                                                        type="time"
                                                        name="time_out[{{ $dateStr }}][{{ $employee->id }}]"
                                                        value="{{ $outVal }}"
                                                        class="time-input">
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-content-save mr-1"></i> Simpan Semua
                        </button>
                        <small class="text-muted ml-3">
                            * Kosongkan kolom IN & OUT untuk menghapus data hari tersebut
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection