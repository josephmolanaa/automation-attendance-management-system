@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ URL::asset('plugins/chartist/css/chartist.min.css') }}">
@endsection

@php
    $wibTime = \Carbon\Carbon::now('Asia/Jakarta');
    $hour = $wibTime->hour;
    if ($hour < 12) {
        $greeting = "Good Morning";
    } elseif ($hour < 18) {
        $greeting = "Good Afternoon";
    } else {
        $greeting = "Good Evening";
    }
    $userName = Auth::user()->name ?? 'Admin';
    $dateString = "It's " . $wibTime->translatedFormat('l, d F Y') . " • " . $wibTime->format('H:i') . " WIB";
@endphp

@section('breadcrumb')
<div class="col-sm-12 text-left mt-3">
    <h4 class="page-title">{{ $greeting }}, {{ $userName }}</h4>
    <p class="page-subtitle">{{ $dateString }}</p>
</div>
@endsection

@section('content')

{{-- 4 Stat Cards — Warm Neutral --}}
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card">
            <div class="card-body">
                <div class="ams-stat-label">Total Karyawan</div>
                <div class="ams-stat-value">{{ $data[0] }}</div>
                <a href="/employees" class="ams-stat-link">Lihat semua →</a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card">
            <div class="card-body">
                <div class="ams-stat-label">Tepat Waktu</div>
                <div class="ams-stat-value" style="color:var(--green)">{{ $data[3] }}%</div>
                <span class="ams-stat-link">Hari ini</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card">
            <div class="card-body">
                <div class="ams-stat-label">Hadir Hari Ini</div>
                <div class="ams-stat-value" style="color:var(--blue)">{{ $data[1] }}</div>
                <a href="/attendance" class="ams-stat-link">Lihat absensi →</a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card">
            <div class="card-body">
                <div class="ams-stat-label">Terlambat</div>
                <div class="ams-stat-value" style="color:var(--red)">{{ $data[2] }}</div>
                <a href="/latetime" class="ams-stat-link">Lihat detail →</a>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

{{-- Monthly Chart --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">Monthly Report</h4>
                <div id="chart-with-area" class="ct-chart earning ct-golden-section"></div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Attendance --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">Recent Attendance</h4>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Tanggal</th>
                                <th>Scan In</th>
                                <th>Scan Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendance as $row)
                            <tr>
                                <td>{{ optional($row->employee)->name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->attendance_time)->format('d M Y') }}</td>
                                <td style="font-family:'DM Mono',monospace;font-size:12px">{{ \Carbon\Carbon::parse($row->attendance_time)->format('H:i') }}</td>
                                <td style="font-family:'DM Mono',monospace;font-size:12px">
                                    @if($row->leave_time)
                                        {{ \Carbon\Carbon::parse($row->leave_time)->format('H:i') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada data attendance</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-right">
                    <a href="/attendance" class="btn btn-sm" style="border-color:var(--border)">Lihat Semua →</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('plugins/chartist/js/chartist.min.js') }}"></script>
<script src="{{ URL::asset('plugins/chartist/js/chartist-plugin-tooltip.min.js') }}"></script>
<script src="{{ URL::asset('plugins/peity-chart/jquery.peity.min.js') }}"></script>
<script src="{{ URL::asset('assets/pages/dashboard.js') }}"></script>
@endsection