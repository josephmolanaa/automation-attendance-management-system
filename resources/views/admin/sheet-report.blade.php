@extends('layouts.master')
@section('content')

    <div class="card">
        <div class="card-header bg-success text-white">
            TimeTable
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="printTable">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Employee Position</th>
                            <th>Employee ID</th>
                            @php
                                $today = today();
                                $dates = [];
                                for ($i = 1; $i < $today->daysInMonth + 1; ++$i) {
                                    $dates[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('Y-m-d');
                                }
                            @endphp
                            @foreach ($dates as $date)
                                <th>{{ $date }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->position }}</td>
                                <td>{{ $employee->emp_id }}</td>

                                @for ($i = 1; $i < $today->daysInMonth + 1; ++$i)
                                    @php
                                        $date_picker = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('Y-m-d');

                                        // Scan 1 (attendance_time) dari checks table
                                        $scan1 = \App\Models\Check::where('emp_id', $employee->id)
                                            ->whereDate('attendance_time', $date_picker)
                                            ->whereNotNull('attendance_time')
                                            ->first();

                                        // Scan 2 (leave_time) dari checks table
                                        $scan2 = \App\Models\Check::where('emp_id', $employee->id)
                                            ->whereDate('leave_time', $date_picker)
                                            ->whereNotNull('leave_time')
                                            ->first();

                                        // Izin/Cuti dari leaves table
                                        $izin = \App\Models\IzinDanCuti::where('emp_id', $employee->id)
                                            ->where('leave_date', $date_picker)
                                            ->first();
                                    @endphp
                                    <td style="text-align:center; white-space:nowrap;">
                                        {{-- Scan In --}}
                                        @if ($scan1)
                                            <i class="fa fa-check text-success" title="Scan In: {{ \Carbon\Carbon::parse($scan1->attendance_time)->format('H:i') }}"></i>
                                        @elseif ($izin)
                                            <i class="fa fa-minus text-warning" title="{{ ucfirst($izin->reason) }}"></i>
                                        @else
                                            <i class="fas fa-times text-danger"></i>
                                        @endif

                                        {{-- Scan Out --}}
                                        @if ($scan2)
                                            <i class="fa fa-check text-success" title="Scan Out: {{ \Carbon\Carbon::parse($scan2->leave_time)->format('H:i') }}"></i>
                                        @elseif ($izin)
                                            <i class="fa fa-minus text-warning" title="{{ ucfirst($izin->reason) }}"></i>
                                        @else
                                            <i class="fas fa-times text-danger"></i>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection