@extends('layouts.master')

@section('css')
    @include('includes.datatable-controls-style')
@endsection

@section('page-title') Manajemen Keterlambatan @endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('lateness.calculate') }}" class="d-flex flex-wrap align-items-end" style="gap:10px;">
                    @csrf
                    <div>
                        <label>Periode Hitung</label>
                        <input type="month" name="month" class="form-control" value="{{ sprintf('%04d-%02d', $filters['tahun'], $filters['bulan']) }}">
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
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="force" name="force" value="1">
                        <label class="custom-control-label" for="force">Hitung ulang</label>
                    </div>
                    <button class="btn btn-primary">
                        <i class="mdi mdi-calculator mr-1"></i> Hitung Keterlambatan
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3" style="gap:12px;">
                    <form method="GET" action="{{ route('lateness.index') }}" class="d-flex flex-wrap align-items-end" style="gap:10px;">
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
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua</option>
                                <option value="terlambat" {{ $filters['status'] === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="tepat_waktu" {{ $filters['status'] === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
                                <option value="tidak_ada_scan" {{ $filters['status'] === 'tidak_ada_scan' ? 'selected' : '' }}>Tidak Ada Scan</option>
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
                        <a class="btn btn-outline-primary" href="{{ route('lateness.recap', request()->query()) }}">Rekap</a>
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
                                <th>Tanggal</th>
                                <th>Hari</th>
                                <th>Scan Masuk</th>
                                <th>Scan Keluar</th>
                                <th>Shift</th>
                                <th>Jadwal Masuk</th>
                                <th>Status</th>
                                <th>Durasi</th>
                                <th>Menit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                @php
                                    $statusClass = [
                                        'terlambat' => 'danger',
                                        'tepat_waktu' => 'success',
                                        'tidak_ada_scan' => 'secondary',
                                    ][$record->status] ?? 'secondary';
                                    $statusText = [
                                        'terlambat' => 'Terlambat',
                                        'tepat_waktu' => 'Tepat Waktu',
                                        'tidak_ada_scan' => 'Tidak Ada Scan',
                                    ][$record->status] ?? $record->status;
                                @endphp
                                <tr>
                                    <td>{{ $record->employee->emp_id ?? $record->employee->id ?? '-' }}</td>
                                    <td>{{ $record->employee->name ?? '-' }}</td>
                                    <td>{{ $record->employee->position ?? '-' }}</td>
                                    <td>{{ optional($record->date)->format('d/m/Y') }}</td>
                                    <td>{{ optional($record->date)->locale('id')->isoFormat('dddd') }}</td>
                                    <td>{{ $record->actual_scan_in ? $record->actual_scan_in->format('H:i:s') : '-' }}</td>
                                    <td>{{ optional(optional($record->check)->leave_time ? \Carbon\Carbon::parse($record->check->leave_time) : null)->format('H:i:s') ?: '-' }}</td>
                                    <td>{{ $record->schedule->slug ?? '-' }}</td>
                                    <td>{{ $record->scheduled_in ? $record->scheduled_in->format('H:i:s') : '-' }}</td>
                                    <td><span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span></td>
                                    <td>{{ $record->late_duration }}</td>
                                    <td>{{ $record->late_minutes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted">Belum ada data. Jalankan hitung keterlambatan untuk periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
