@extends('layouts.master')

@section('page-title') Rekap Keterlambatan @endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3" style="gap:12px;">
                    <form method="GET" action="{{ route('lateness.recap') }}" class="d-flex flex-wrap align-items-end" style="gap:10px;">
                        <div>
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $filters['bulan'] == $m ? 'selected' : '' }}>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                @foreach(range(date('Y') + 1, 2024) as $year)
                                    <option value="{{ $year }}" {{ $filters['tahun'] == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Karyawan</label>
                            <select name="employee" class="form-control" style="min-width:180px;">
                                <option value="">Semua karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ (string) $filters['employee'] === (string) $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-secondary">Filter</button>
                    </form>

                    <div class="d-flex align-items-end" style="gap:8px;">
                        <a class="btn btn-outline-primary" href="{{ route('lateness.index', request()->query()) }}">Data Scanlog</a>
                        <a class="btn btn-success" href="{{ route('lateness.export', request()->query()) }}">
                            <i class="mdi mdi-file-excel mr-1"></i> Export Excel
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Total Hari Terlambat</th>
                                <th>Total Menit</th>
                                <th>Total Durasi</th>
                                <th>Rata-rata Menit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recap as $row)
                                <tr class="{{ $row['total_late_days'] > 3 ? 'table-danger' : '' }}">
                                    <td>{{ $row['employee']->emp_id ?? $row['employee']->id ?? '-' }}</td>
                                    <td>{{ $row['employee']->name ?? '-' }}</td>
                                    <td>{{ $row['employee']->position ?? '-' }}</td>
                                    <td>{{ $row['total_late_days'] }}</td>
                                    <td>{{ $row['total_minutes'] }}</td>
                                    <td>{{ $row['total_duration'] }}</td>
                                    <td>{{ $row['average_minutes'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data keterlambatan untuk periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
