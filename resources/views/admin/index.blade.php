@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ URL::asset('plugins/chartist/css/chartist.min.css') }}">
@endsection

@php
    $wibTime = \Carbon\Carbon::now('Asia/Jakarta');
    $hour = $wibTime->hour;
    if ($hour < 12) {
        $greeting = __('app.good_morning');
    } elseif ($hour < 18) {
        $greeting = __('app.good_afternoon');
    } else {
        $greeting = __('app.good_evening');
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
                <div class="ams-stat-label">{{ __('app.total_employees') }}</div>
                <div class="ams-stat-value">{{ $data[0] }}</div>
                <a href="/employees" class="ams-stat-link">{{ __('app.view_all') }} →</a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card">
            <div class="card-body">
                <div class="ams-stat-label">{{ __('app.on_time_percentage') }}</div>
                <div class="ams-stat-value" style="color:var(--green)">{{ $data[3] }}%</div>
                <span class="ams-stat-link">{{ __('app.today') }}</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card">
            <div class="card-body">
                <div class="ams-stat-label">{{ __('app.present_today') }}</div>
                <div class="ams-stat-value" style="color:var(--blue)">{{ $data[1] }}</div>
                <a href="/attendance" class="ams-stat-link">{{ __('app.view_attendance') }} →</a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card ams-stat-card">
            <div class="card-body">
                <div class="ams-stat-label">{{ __('app.late_today') }}</div>
                <div class="ams-stat-value" style="color:var(--red)">{{ $data[2] }}</div>
                <a href="/latetime" class="ams-stat-link">{{ __('app.view_details') }} →</a>
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
                <h4 class="mt-0 header-title mb-4">{{ __('app.monthly_report') }}</h4>
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
                <h4 class="mt-0 header-title mb-4">{{ __('app.recent_attendance') }}</h4>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('app.name') }}</th>
                                <th>{{ __('app.date') }}</th>
                                <th>{{ __('app.scan_in') }}</th>
                                <th>{{ __('app.scan_out') }}</th>
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
                                <td colspan="4" class="text-center text-muted">{{ __('app.no_attendance_data') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-right">
                    <a href="/attendance" class="btn btn-sm" style="border-color:var(--border)">{{ __('app.view_all') }} →</a>
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
<!-- Removed static dashboard.js -->
<script>
    $(document).ready(function() {
        // Line chart with area
        new Chartist.Line('#chart-with-area', {
            labels: {!! json_encode($chartLabels) !!},
            series: [
                {!! json_encode($chartData) !!}
            ]
        }, {
            low: 0,
            showArea: true,
            plugins: [
                Chartist.plugins.tooltip()
            ],
            axisY: {
                onlyInteger: true
            }
        });
        
        // Donut and Line (Peity) fallback if still used elsewhere
        $('.peity-donut').each(function () {
            $(this).peity("donut", $(this).data());
        });
        $('.peity-line').each(function() {
            $(this).peity("line", $(this).data());
        });
    });
</script>
@endsection