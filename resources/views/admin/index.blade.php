@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ URL::asset('plugins/chartist/css/chartist.min.css') }}">
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title">Dashboard</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Welcome to Attendance Management System</li>
    </ol>
</div>
@endsection

@section('content')

{{-- 4 Stat Cards - Dark Native --}}
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card" style="border-left: 3px solid #3B82F6;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ams-stat-label">Total Employees</div>
                        <div class="ams-stat-value">{{ $data[0] }}</div>
                    </div>
                    <div class="ams-stat-icon" style="color: #3B82F6;"><i class="ti-id-badge"></i></div>
                </div>
                <a href="/employees" class="ams-stat-link">Lihat semua →</a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card" style="border-left: 3px solid #22C55E;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ams-stat-label">On Time %</div>
                        <div class="ams-stat-value">{{ $data[3] }}%</div>
                    </div>
                    <div class="ams-stat-icon" style="color: #22C55E;"><i class="ti-alarm-clock"></i></div>
                </div>
                <div class="ams-stat-link" style="opacity:0.5;">Hari ini</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card" style="border-left: 3px solid #06B6D4;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ams-stat-label">On Time Today</div>
                        <div class="ams-stat-value">{{ $data[1] }}</div>
                    </div>
                    <div class="ams-stat-icon" style="color: #06B6D4;"><i class="ti-check-box"></i></div>
                </div>
                <a href="/attendance" class="ams-stat-link">Lihat attendance →</a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card" style="border-left: 3px solid #EF4444;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ams-stat-label">Late Today</div>
                        <div class="ams-stat-value">{{ $data[2] }}</div>
                    </div>
                    <div class="ams-stat-icon" style="color: #EF4444;"><i class="ti-alert"></i></div>
                </div>
                <a href="/latetime" class="ams-stat-link">Lihat late time →</a>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

{{-- Chart + Recent Attendance --}}
<div class="row">
    {{-- Monthly Chart --}}
    <div class="col-xl-5">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">Monthly Report</h4>
                <div id="chart-with-area" class="ct-chart earning ct-golden-section"></div>
            </div>
        </div>
    </div>

    {{-- Recent Attendance --}}
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">Recent Attendance</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
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
                                <td>{{ \Carbon\Carbon::parse($row->attendance_time)->format('H:i') }}</td>
                                <td>
                                    @if($row->leave_time)
                                        {{ \Carbon\Carbon::parse($row->leave_time)->format('H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
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
                    <a href="/attendance" class="btn btn-sm btn-outline-primary">Lihat Semua <i class="mdi mdi-arrow-right"></i></a>
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