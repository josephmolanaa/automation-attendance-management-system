@extends('layouts.master')

@section('css')
<style>
/* ── Upload Zone ─────────────────────────────────────────── */
.upload-zone {
    border: 2.5px dashed #4472C4;
    border-radius: 16px;
    background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
    padding: 60px 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: #2856c8;
    background: linear-gradient(135deg, #e0eaff 0%, #d2e3fc 100%);
    transform: scale(1.01);
    box-shadow: 0 8px 30px rgba(68,114,196,0.18);
}
.upload-zone .upload-icon {
    font-size: 64px;
    color: #4472C4;
    margin-bottom: 16px;
    display: block;
}
.upload-zone .upload-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a3a7a;
    margin-bottom: 8px;
}
.upload-zone .upload-subtitle {
    font-size: 14px;
    color: #6b7c9e;
}
.upload-zone .file-name-badge {
    display: inline-block;
    background: #4472C4;
    color: #fff;
    border-radius: 20px;
    padding: 6px 18px;
    font-size: 13px;
    font-weight: 600;
    margin-top: 12px;
}
#pdf_file { display: none; }

/* ── Config Bar ─────────────────────────────────────────── */
.config-bar {
    background: #fff;
    border-radius: 12px;
    padding: 20px 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    margin-top: 20px;
}

/* ── Progress ───────────────────────────────────────────── */
#progressSection { display: none; }
.progress-lbl {
    font-size: 13px;
    font-weight: 600;
    color: #4472C4;
    margin-bottom: 6px;
}
#progressBar {
    height: 10px;
    border-radius: 10px;
    transition: width 0.4s;
}

/* ── Preview Table ──────────────────────────────────────── */
#previewSection { display: none; margin-top: 24px; }
.preview-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}
.preview-header {
    background: linear-gradient(135deg, #4472C4, #2856c8);
    color: #fff;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.preview-header h5 { margin: 0; font-weight: 700; font-size: 16px; }
.employee-block {
    border-bottom: 1px solid #e8edf5;
    padding: 20px 24px;
}
.employee-block:last-child { border-bottom: none; }
.employee-name {
    font-size: 15px;
    font-weight: 700;
    color: #1a3a7a;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.emp-badge {
    background: #4472C4;
    color: #fff;
    border-radius: 50%;
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
    flex-shrink: 0;
}
.scan-table { width: 100%; font-size: 13px; border-collapse: collapse; }
.scan-table th {
    background: #f0f4ff;
    color: #4472C4;
    font-weight: 700;
    padding: 8px 12px;
    text-align: center;
    border-bottom: 2px solid #d0ddf0;
}
.scan-table td {
    padding: 7px 12px;
    text-align: center;
    border-bottom: 1px solid #f0f4ff;
    color: #333;
}
.scan-table tr:hover td { background: #f8f9ff; }
.lembur-normal { background: #e3f2fd; color: #1565c0; border-radius: 8px; padding: 3px 10px; font-weight: 600; }
.lembur-double { background: #fff3e0; color: #e65100; border-radius: 8px; padding: 3px 10px; font-weight: 600; }
.lembur-minggu { background: #fce4ec; color: #c62828; border-radius: 8px; padding: 3px 10px; font-weight: 600; }
.no-scan { color: #bbb; font-style: italic; font-size: 12px; }
.row-minggu td { background: #fff0f3 !important; color: #c62828 !important; font-weight: 600; }

/* ── Export Button ──────────────────────────────────────── */
#btnExportExcel {
    background: linear-gradient(135deg, #217346, #1a5c38);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 12px 28px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(33,115,70,0.3);
}
#btnExportExcel:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(33,115,70,0.4);
}
#btnExportExcel:disabled {
    background: #aaa;
    transform: none;
    box-shadow: none;
    cursor: not-allowed;
}

/* ── Alert ──────────────────────────────────────────────── */
.alert-ocr {
    border-radius: 10px;
    padding: 14px 18px;
    font-size: 14px;
    margin-top: 16px;
    display: none;
}
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Upload Scanlog PDF</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item active">Upload Scanlog → Export Lembur Harian</li>
    </ol>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        {{-- ── STEP 1: Upload ─────────────────────────────────────────── --}}
        <div class="card" style="border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
            <div class="card-body" style="padding:28px;">
                <h5 style="color:#1a3a7a; font-weight:700; margin-bottom:6px;">
                    <i class="mdi mdi-file-upload-outline mr-2" style="color:#4472C4;"></i>
                    Upload Kartu Scanlog (PDF CamScanner)
                </h5>
                <p style="color:#6b7c9e; font-size:13px; margin-bottom:20px;">
                    Upload file PDF hasil CamScanner berisi data absensi karyawan. Sistem akan otomatis membaca,
                    menghitung lembur, dan menghasilkan file Excel format <strong>Lembur Harian</strong>.
                </p>

                {{-- Drag & Drop Zone --}}
                <div class="upload-zone" id="uploadZone">
                    <i class="mdi mdi-file-pdf-box upload-icon"></i>
                    <div class="upload-title">Drag & Drop PDF di sini</div>
                    <div class="upload-subtitle">atau klik untuk memilih file</div>
                    <div id="fileNameDisplay"></div>
                    <input type="file" id="pdf_file" accept=".pdf">
                </div>

                {{-- Config: Bulan & Tahun --}}
                <div class="config-bar">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-6">
                            <label style="font-weight:600; font-size:13px; color:#4472C4;">Bulan Laporan</label>
                            <select id="selectBulan" class="form-control" style="border-radius:8px;">
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-6">
                            <label style="font-weight:600; font-size:13px; color:#4472C4;">Tahun</label>
                            <select id="selectTahun" class="form-control" style="border-radius:8px;">
                                @foreach(range(date('Y'), 2024) as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-12 mt-2 mt-md-0">
                            <button id="btnUpload" class="btn btn-primary btn-block"
                                style="border-radius:10px; font-weight:700; height:38px; font-size:14px;" disabled>
                                <i class="mdi mdi-magnify-scan mr-1"></i> Proses OCR
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div id="progressSection" style="margin-top:18px;">
                    <div class="progress-lbl" id="progressLabel">Mengkonversi PDF ke gambar...</div>
                    <div class="progress" style="border-radius:10px; height:10px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                            role="progressbar" style="width:0%"></div>
                    </div>
                </div>

                {{-- Alert error --}}
                <div class="alert alert-danger alert-ocr" id="alertError">
                    <i class="mdi mdi-alert-circle mr-2"></i>
                    <span id="errorMsg"></span>
                    <div id="errorDebug" style="display:none; margin-top:10px;">
                        <small><strong>Detail teknis:</strong></small>
                        <pre id="errorDebugText" style="font-size:11px; max-height:200px; overflow:auto; background:#fff3f3; padding:8px; border-radius:6px; margin-top:4px;"></pre>
                    </div>
                </div>

                {{-- Debug OCR Button --}}
                <div id="debugSection" style="display:none; margin-top:12px; padding:12px; background:#fffbeb; border-radius:10px; border:1px solid #fde68a;">
                    <div style="font-size:13px; color:#92400e; margin-bottom:8px; font-weight:600;">
                        <i class="mdi mdi-bug mr-1"></i> Debug: Lihat teks mentah hasil OCR
                    </div>
                    <button id="btnDebugOcr" class="btn btn-warning btn-sm">
                        <i class="mdi mdi-text-search mr-1"></i> Jalankan Debug OCR
                    </button>
                    <pre id="debugOcrResult" style="display:none; font-size:11px; max-height:300px; overflow:auto; background:#fefce8; padding:10px; border-radius:6px; margin-top:10px; white-space:pre-wrap;"></pre>
                </div>
            </div>
        </div>

        {{-- ── STEP 2: Preview & Export ───────────────────────────────── --}}
        <div id="previewSection">
            <div class="preview-card">
                <div class="preview-header">
                    <h5><i class="mdi mdi-table-eye mr-2"></i>Preview Hasil OCR</h5>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span id="previewSummary" class="badge badge-light" style="font-size:13px; color:#1a3a7a; padding:6px 14px;"></span>
                        <button id="btnExportExcel" disabled>
                            <i class="mdi mdi-microsoft-excel" style="font-size:20px;"></i>
                            Download Excel Lembur Harian
                        </button>
                    </div>
                </div>
                <div id="previewBody" style="padding: 0;"></div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script-bottom')
<script>
$(function() {
    var currentEmployees = null;

    // ── Set default bulan = bulan sekarang ───────────────────────────────
    $('#selectBulan').val(new Date().getMonth() + 1);

    // ── Drag & Drop ──────────────────────────────────────────────────────
    var uploadZone = document.getElementById('uploadZone');
    var fileInput  = document.getElementById('pdf_file');

    uploadZone.addEventListener('click', () => fileInput.click());

    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        var files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type === 'application/pdf') {
            fileInput.files = files;
            handleFileSelected(files[0]);
        } else {
            showError('Hanya file PDF yang diizinkan.');
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleFileSelected(this.files[0]);
        }
    });

    function handleFileSelected(file) {
        var sizeMB = (file.size / 1024 / 1024).toFixed(2);
        $('#fileNameDisplay').html(
            '<div class="file-name-badge"><i class="mdi mdi-file-pdf-box mr-1"></i>' +
            file.name + ' (' + sizeMB + ' MB)</div>'
        );
        $('#btnUpload').prop('disabled', false);
        hideError();
        $('#previewSection').hide();
        currentEmployees = null;
        $('#btnExportExcel').prop('disabled', true);
    }

    // ── Proses OCR ───────────────────────────────────────────────────────
    $('#btnUpload').on('click', function() {
        if (!fileInput.files || !fileInput.files[0]) {
            showError('Pilih file PDF terlebih dahulu.');
            return;
        }

        hideError();
        $('#previewSection').hide();
        showProgress('Mengunggah PDF ke server...', 15);

        var formData = new FormData();
        formData.append('pdf_file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        // Simulasi progress karena OCR butuh waktu
        var prog = 15;
        var progMessages = [
            { pct: 30,  msg: 'Mengkonversi PDF ke gambar (DPI 300)...' },
            { pct: 55,  msg: 'Menjalankan OCR pada setiap halaman...' },
            { pct: 75,  msg: 'Mem-parsing nama dan waktu scan...' },
            { pct: 90,  msg: 'Memvalidasi data hasil scan...' },
        ];
        var msgIdx = 0;
        var progTimer = setInterval(function() {
            if (prog < 90) {
                prog += 3;
                if (msgIdx < progMessages.length && prog >= progMessages[msgIdx].pct) {
                    showProgress(progMessages[msgIdx].msg, prog);
                    msgIdx++;
                } else {
                    updateProgressBar(prog);
                }
            }
        }, 800);

        $.ajax({
            url: '{{ route("scanlog.process") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 300000, // 5 menit timeout
            success: function(response) {
                clearInterval(progTimer);
                showProgress('Berhasil membaca data!', 100);

                setTimeout(function() {
                    $('#progressSection').hide();
                    if (response.success && response.employees && response.employees.length > 0) {
                        currentEmployees = response.employees;
                        renderPreview(response.employees);
                    } else {
                        showError('Tidak ada data yang berhasil dibaca. Pastikan PDF berisi teks yang jelas.');
                    }
                }, 500);
            },
            error: function(xhr) {
                clearInterval(progTimer);
                $('#progressSection').hide();
                var msg = 'Gagal memproses PDF.';
                var debugInfo = null;

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    if (xhr.responseJSON.debug)   debugInfo = JSON.stringify(xhr.responseJSON.debug, null, 2);
                } else if (xhr.status === 0) {
                    msg = 'Koneksi timeout atau request dibatalkan. Coba lagi.';
                } else if (xhr.status === 500) {
                    msg = 'Server error. Cek log Railway.';
                }

                showError(msg, debugInfo);

                // Tampilkan tombol debug jika ada file
                if (fileInput.files && fileInput.files[0]) {
                    $('#debugSection').show();
                }
            }
        });
    });

    // ── Debug OCR ────────────────────────────────────────────────────────
    $('#btnDebugOcr').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Memproses...');
        
        var formData = new FormData();
        formData.append('pdf_file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("scanlog.debug.ocr") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#debugOcrResult').text(res.raw_text).show();
                $btn.prop('disabled', false).text('Jalankan Debug OCR');
            },
            error: function() {
                alert('Gagal mengambil data debug.');
                $btn.prop('disabled', false).text('Jalankan Debug OCR');
            }
        });
    });

    // ── Render Preview ───────────────────────────────────────────────────
    function renderPreview(employees) {
        var bulan = parseInt($('#selectBulan').val());
        var tahun = parseInt($('#selectTahun').val());

        // Hitung total hari dalam bulan
        var daysInMonth = new Date(tahun, bulan, 0).getDate();

        var bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni',
                          'Juli','Agustus','September','Oktober','November','Desember'];
        var hariNames  = ['MINGGU','SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU'];

        var bodyHtml = '';
        var totalRecords = 0;

        employees.forEach(function(emp, empIdx) {
            var recordsByDate = {};
            (emp.records || []).forEach(function(r) {
                recordsByDate[r.tanggal] = r;
            });

            totalRecords += (emp.records || []).length;

            bodyHtml += '<div class="employee-block">';
            bodyHtml += '<div class="employee-name">';
            bodyHtml += '<div class="emp-badge">' + (empIdx + 1) + '</div>';
            bodyHtml += '<span>' + escapeHtml(emp.nama) + '</span>';
            bodyHtml += '</div>';

            bodyHtml += '<div class="table-responsive">';
            bodyHtml += '<table class="scan-table">';
            bodyHtml += '<thead><tr>';
            bodyHtml += '<th>Hari</th><th>Tanggal</th><th>Scan 1</th><th>Scan 2</th>';
            bodyHtml += '<th>Normal</th><th>Double</th><th>Minggu</th>';
            bodyHtml += '</tr></thead><tbody>';

            var totalNormal = 0, totalDouble = 0, totalMinggu = 0;

            for (var d = 1; d <= daysInMonth; d++) {
                var mm   = String(bulan).padStart(2, '0');
                var dd   = String(d).padStart(2, '0');
                var dateStr = tahun + '-' + mm + '-' + dd;
                var dateObj = new Date(dateStr);
                var dayOfWeek = dateObj.getDay(); // 0=Minggu
                var hariStr  = hariNames[dayOfWeek];
                var rec      = recordsByDate[dateStr] || {};
                var scan1    = rec.scan1 || '';
                var scan2    = rec.scan2 || '';
                var isMinggu = dayOfWeek === 0;

                var normal = 0, dbl = 0, minggu = 0;
                if (scan1 && scan2) {
                    var ot = calcOvertime(scan1, scan2, dateStr, dayOfWeek);
                    normal = ot.normal; dbl = ot.double; minggu = ot.minggu;
                } else if (isMinggu && scan1) {
                    minggu = 1;
                }
                totalNormal += normal; totalDouble += dbl; totalMinggu += minggu;

                // Hanya tampilkan baris yang ada data
                if (!scan1 && !scan2) continue;

                var rowClass = isMinggu ? 'row-minggu' : '';
                bodyHtml += '<tr class="' + rowClass + '">';
                bodyHtml += '<td>' + hariStr + '</td>';
                bodyHtml += '<td>' + dd + '/' + mm + '/' + tahun + '</td>';
                bodyHtml += '<td>' + (scan1 || '<span class="no-scan">—</span>') + '</td>';
                bodyHtml += '<td>' + (scan2 || '<span class="no-scan">—</span>') + '</td>';
                bodyHtml += '<td>' + (normal ? '<span class="lembur-normal">' + normal + ' jam</span>' : '—') + '</td>';
                bodyHtml += '<td>' + (dbl    ? '<span class="lembur-double">' + dbl + ' jam</span>' : '—') + '</td>';
                bodyHtml += '<td>' + (minggu ? '<span class="lembur-minggu">✓</span>' : '—') + '</td>';
                bodyHtml += '</tr>';
            }

            // Total row
            bodyHtml += '<tr style="background:#f0f4ff; font-weight:700;">';
            bodyHtml += '<td colspan="4" style="text-align:right; color:#1a3a7a; padding-right:16px;">TOTAL</td>';
            bodyHtml += '<td><span class="lembur-normal">' + totalNormal + '</span></td>';
            bodyHtml += '<td><span class="lembur-double">' + totalDouble + '</span></td>';
            bodyHtml += '<td><span class="lembur-minggu">' + totalMinggu + '</span></td>';
            bodyHtml += '</tr>';

            bodyHtml += '</tbody></table></div>';
            bodyHtml += '</div>'; // employee-block
        });

        $('#previewBody').html(bodyHtml);
        $('#previewSummary').text(employees.length + ' karyawan · ' + totalRecords + ' record scan');
        $('#previewSection').fadeIn(400);
        $('#btnExportExcel').prop('disabled', false);

        // Scroll ke preview
        $('html, body').animate({ scrollTop: $('#previewSection').offset().top - 20 }, 600);
    }

    // ── Hitung Lembur (JS, sama dengan logic PHP) ────────────────────────
    function calcOvertime(scan1, scan2, dateStr, dayOfWeek) {
        if (dayOfWeek === 0) return { normal: 0, double: 0, minggu: 1 }; // Minggu

        var shifts = [
            { day_type: 'weekday',  time_in: '08:00', time_out: '17:00', days: [1,2,3,4,5] },
            { day_type: 'weekday',  time_in: '19:00', time_out: '03:00', days: [1,2,3,4,5] },
            { day_type: 'saturday', time_in: '08:00', time_out: '13:00', days: [6] },
            { day_type: 'saturday', time_in: '13:00', time_out: '17:00', days: [6] },
            { day_type: 'holiday',  time_in: '08:00', time_out: '17:00', days: [0] },
        ];

        var is_saturday = dayOfWeek === 6;
        var day_type = is_saturday ? 'saturday' : 'weekday';

        var scan1h = parseInt(scan1.split(':')[0]);
        var matched = null;
        shifts.forEach(function(s) {
            if (s.day_type !== day_type) return;
            var sH = parseInt(s.time_in.split(':')[0]);
            var diff = Math.abs(scan1h - sH);
            diff = Math.min(diff, 24 - diff);
            if (diff <= 3 && !matched) matched = s;
        });
        if (!matched) return { normal: 0, double: 0, minggu: 0 };

        var base = new Date(dateStr + 'T00:00:00');
        var outParts = matched.time_out.split(':');
        var schedOut = new Date(dateStr + 'T' + matched.time_out + ':00');
        var inParts  = matched.time_in.split(':');
        var schedIn  = new Date(dateStr + 'T' + matched.time_in + ':00');
        if (schedOut <= schedIn) schedOut.setDate(schedOut.getDate() + 1);

        var scan2dt = new Date(dateStr + 'T' + scan2);
        if (scan2dt < schedIn) scan2dt.setDate(scan2dt.getDate() + 1);

        var diffSec = (scan2dt - schedOut) / 1000;
        var tol = 55 * 60;
        if (diffSec <= tol) return { normal: 0, double: 0, minggu: 0 };

        var h = Math.floor(diffSec / 3600);
        if (h <= 3) return { normal: h, double: 0, minggu: 0 };
        return { normal: 3, double: h - 3, minggu: 0 };
    }

    // ── Export Excel ─────────────────────────────────────────────────────
    $('#btnExportExcel').on('click', function() {
        if (!currentEmployees) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mr-2"></i>Membuat Excel...');

        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('bulan', $('#selectBulan').val());
        formData.append('tahun', $('#selectTahun').val());
        formData.append('data', JSON.stringify(currentEmployees));

        fetch('{{ route("scanlog.export") }}', {
            method: 'POST',
            body: formData,
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Gagal mengunduh Excel.');
            return response.blob();
        })
        .then(function(blob) {
            var bulan = $('#selectBulan').val().padStart(2, '0');
            var tahun = $('#selectTahun').val();
            var url  = URL.createObjectURL(blob);
            var a    = document.createElement('a');
            a.href   = url;
            a.download = 'Lembur_Harian_' + tahun + '_' + bulan + '.xlsx';
            a.click();
            URL.revokeObjectURL(url);

            $btn.prop('disabled', false).html('<i class="mdi mdi-microsoft-excel" style="font-size:20px;"></i> Download Excel Lembur Harian');
        })
        .catch(function(err) {
            showError('Gagal membuat file Excel: ' + err.message);
            $btn.prop('disabled', false).html('<i class="mdi mdi-microsoft-excel" style="font-size:20px;"></i> Download Excel Lembur Harian');
        });
    });

    // ── Helper functions ─────────────────────────────────────────────────
    function showProgress(msg, pct) {
        $('#progressSection').show();
        $('#progressLabel').text(msg);
        $('#progressBar').css('width', pct + '%').attr('aria-valuenow', pct);
    }

    function updateProgressBar(pct) {
        $('#progressBar').css('width', pct + '%').attr('aria-valuenow', pct);
    }

    function showError(msg, debugInfo) {
        $('#errorMsg').text(msg);
        if (debugInfo) {
            $('#errorDebugText').text(debugInfo);
            $('#errorDebug').show();
        } else {
            $('#errorDebug').hide();
        }
        $('#alertError').fadeIn(200);
    }

    function hideError() {
        $('#alertError').hide();
        $('#debugSection').hide();
        $('#debugOcrResult').hide().text('');
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
});
</script>
@endsection
