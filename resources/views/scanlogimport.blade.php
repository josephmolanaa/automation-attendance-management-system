@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Import Scanlog Fingerprint</h4>
                </div>
                <div class="card-body">

                    {{-- Alert sukses --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            <strong>✅ {{ session('success') }}</strong>
                        </div>
                    @endif

                    {{-- Alert error --}}
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <strong>❌ {{ session('error') }}</strong>
                        </div>
                    @endif

                    {{-- Flagged anomali --}}
                    @if(session('flagged') && count(session('flagged')) > 0)
                        <div class="alert alert-warning">
                            <strong>⚠️ Beberapa data perlu perhatian manual:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach(session('flagged') as $flag)
                                    <li>{{ $flag }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Validasi error --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $err)
                                <p class="mb-0">{{ $err }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('scanlog.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">File Excel Scanlog <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                            <div class="form-text">
                                Format kolom: <code>Nama | Tanggal | Scan 1 | Scan 2 | Scan 3 (opsional)</code><br>
                                Tanggal: <code>dd-mm-yyyy</code> atau <code>yyyy-mm-dd</code><br>
                                Waktu: <code>HH:MM</code> atau <code>HH:MM:SS</code>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> Proses Import
                        </button>
                    </form>

                    {{-- Panduan logic --}}
                    <hr class="mt-4">
                    <h6 class="text-muted">Logic Penentuan Scan In / Out</h6>
                    <table class="table table-sm table-bordered text-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Jam Scan</th>
                                <th>Interpretasi</th>
                                <th>Kondisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>00:00–05:59</code></td>
                                <td>Timeout shift malam</td>
                                <td>Isi leave_time open check kemarin/hari ini</td>
                            </tr>
                            <tr>
                                <td><code>06:00–11:59</code></td>
                                <td>Ambigu</td>
                                <td>Ada open check kemarin → timeout. Tidak ada → time_in baru</td>
                            </tr>
                            <tr>
                                <td><code>12:00–15:59</code></td>
                                <td>Sabtu siang</td>
                                <td>Ada open check hari ini → timeout. Tidak ada → time_in baru</td>
                            </tr>
                            <tr>
                                <td><code>16:00–18:59</code></td>
                                <td>Lembur / Shift 2 Friday</td>
                                <td>Ada open check hari ini → timeout lembur. Tidak ada → time_in shift_2_friday</td>
                            </tr>
                            <tr>
                                <td><code>19:00–23:59</code></td>
                                <td>Lembur / Shift 2</td>
                                <td>Ada open check hari ini → timeout lembur. Tidak ada → time_in shift_2</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-muted small">
                        ℹ️ Scan masuk tanpa scan keluar → tetap dicatat sebagai hadir, <code>leave_time = NULL</code>.
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection