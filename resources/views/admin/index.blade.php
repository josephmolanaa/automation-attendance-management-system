@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ URL::asset('plugins/chartist/css/chartist.min.css') }}">
<style>
    .mini-stat-icon { font-size: 36px; opacity: 0.6; }
    .stat-value { font-size: 28px; font-weight: 600; }
</style>
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

{{-- 4 Stat Cards --}}
<div class="row">
    {{-- Total Employees --}}
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="text-white-50 mb-1 text-uppercase font-13">Total Employees</p>
                        <h3 class="stat-value mb-0">{{ $data[0] }}</h3>
                    </div>
                    <span class="ti-id-badge mini-stat-icon"></span>
                </div>
                <p class="text-white-50 mb-0"><a href="/employees" class="text-white-50">Lihat semua karyawan <i class="mdi mdi-arrow-right"></i></a></p>
            </div>
        </div>
    </div>

    {{-- On Time % --}}
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="text-white-50 mb-1 text-uppercase font-13">On Time %</p>
                        <h3 class="stat-value mb-0">{{ $data[3] }}%</h3>
                    </div>
                    <span class="ti-alarm-clock mini-stat-icon"></span>
                </div>
                <p class="text-white-50 mb-0">Persentase tepat waktu hari ini</p>
            </div>
        </div>
    </div>

    {{-- On Time Today --}}
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="text-white-50 mb-1 text-uppercase font-13">On Time Today</p>
                        <h3 class="stat-value mb-0">{{ $data[1] }}</h3>
                    </div>
                    <span class="ti-check-box mini-stat-icon"></span>
                </div>
                <p class="text-white-50 mb-0"><a href="/attendance" class="text-white-50">Lihat attendance <i class="mdi mdi-arrow-right"></i></a></p>
            </div>
        </div>
    </div>

    {{-- Late Today --}}
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="text-white-50 mb-1 text-uppercase font-13">Late Today</p>
                        <h3 class="stat-value mb-0">{{ $data[2] }}</h3>
                    </div>
                    <span class="ti-alert mini-stat-icon"></span>
                </div>
                <p class="text-white-50 mb-0"><a href="/latetime" class="text-white-50">Lihat late time <i class="mdi mdi-arrow-right"></i></a></p>
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