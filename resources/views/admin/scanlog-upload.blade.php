@extends('layouts.master')

@section('css')
<style>
/* ══ Base ═══════════════════════════════════════════════════════════════ */
.import-page { max-width: 1100px; margin: 0 auto; }

/* ══ Step Pills ══════════════════════════════════════════════════════════ */
.step-bar { display:flex; align-items:center; margin-bottom:28px; }
.step-pill {
    display:flex; align-items:center; gap:10px;
    padding:10px 20px; border-radius:30px;
    font-size:13px; font-weight:700; color:#94a3b8;
    background:#f1f5f9; border:2px solid transparent;
    transition:all 0.3s; flex-shrink:0;
}
.step-pill .step-num {
    width:24px; height:24px; border-radius:50%;
    background:#cbd5e1; color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:800; flex-shrink:0;
}
.step-pill.active { background:#eff6ff; border-color:#3b82f6; color:#1e40af; }
.step-pill.active .step-num { background:#3b82f6; }
.step-pill.done   { background:#f0fdf4; border-color:#22c55e; color:#15803d; }
.step-pill.done .step-num { background:#22c55e; }
.step-connector { flex:1; height:2px; background:#e2e8f0; margin:0 8px; min-width:20px; }
.step-connector.done { background:#22c55e; }

/* ══ Card ════════════════════════════════════════════════════════════════ */
.import-card { background:#fff; border-radius:16px; box-shadow:0 2px 16px rgba(0,0,0,0.07); margin-bottom:20px; overflow:hidden; }
.import-card-header { padding:18px 24px; font-size:15px; font-weight:700; display:flex; align-items:center; gap:10px; border-bottom:1px solid #f1f5f9; }
.import-card-body { padding:24px; }

/* ══ Template Download Bar ═══════════════════════════════════════════════ */
.template-bar {
    background:linear-gradient(135deg,#eff6ff,#f0fdf4);
    border:1.5px dashed #93c5fd; border-radius:12px;
    padding:16px 20px; display:flex; align-items:center;
    justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:20px;
}
.template-bar .tip { font-size:13px; color:#1e40af; font-weight:600; }
.template-bar .tip small { display:block; font-weight:400; color:#475569; margin-top:2px; }
.btn-template {
    background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff;
    border:none; border-radius:8px; padding:9px 18px;
    font-size:13px; font-weight:700; cursor:pointer;
    display:flex; align-items:center; gap:7px; transition:all 0.2s;
    text-decoration:none; flex-shrink:0;
}
.btn-template:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(59,130,246,.35); color:#fff; text-decoration:none; }

/* ══ Upload Zone ═════════════════════════════════════════════════════════ */
.upload-zone {
    border:2.5px dashed #93c5fd; border-radius:14px;
    background:linear-gradient(135deg,#f8faff,#f0f9ff);
    padding:48px 24px; text-align:center; cursor:pointer; transition:all 0.25s;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color:#3b82f6; background:linear-gradient(135deg,#eff6ff,#e0f2fe);
    transform:scale(1.01); box-shadow:0 6px 24px rgba(59,130,246,.15);
}
.upload-zone .upload-icon { font-size:52px; color:#3b82f6; display:block; margin-bottom:12px; }
.upload-zone .upload-title { font-size:18px; font-weight:700; color:#1e3a8a; margin-bottom:6px; }
.upload-zone .upload-sub   { font-size:13px; color:#64748b; }
.file-badge { display:inline-flex; align-items:center; gap:8px; background:#3b82f6; color:#fff; border-radius:20px; padding:6px 16px; font-size:13px; font-weight:600; margin-top:14px; }
#csv_file { display:none; }

/* ══ Format Info ═════════════════════════════════════════════════════════ */
.format-info { background:#fefce8; border:1px solid #fde047; border-radius:10px; padding:14px 18px; margin-top:16px; font-size:12px; color:#713f12; }
.format-info code { background:#fef9c3; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:700; }

/* ══ Progress ════════════════════════════════════════════════════════════ */
#progressSection { display:none; margin-top:16px; }
.progress-lbl { font-size:13px; font-weight:600; color:#3b82f6; margin-bottom:6px; }
#progressBar { height:10px; border-radius:10px; transition:width 0.4s; }

/* ══ Alert ═══════════════════════════════════════════════════════════════ */
.alrt { display:none; border-radius:10px; padding:14px 18px; font-size:13px; margin-top:16px; }

/* ══ Preview ═════════════════════════════════════════════════════════════ */
#previewSection { display:none; }
.preview-header-bar {
    background:linear-gradient(135deg,#1e40af,#1d4ed8); color:#fff;
    padding:16px 24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
}
.preview-header-bar h5 { margin:0; font-size:15px; font-weight:700; }
.preview-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.btn-import {
    background:linear-gradient(135deg,#7c3aed,#5b21b6);
    color:#fff; border:none; border-radius:8px;
    padding:10px 20px; font-size:13px; font-weight:700;
    cursor:pointer; display:flex; align-items:center; gap:7px;
    transition:all 0.2s; box-shadow:0 4px 14px rgba(124,58,237,.3);
}
.btn-import:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(124,58,237,.4); }
.btn-import:disabled { background:#94a3b8; transform:none; box-shadow:none; cursor:not-allowed; }

/* ══ Create New Bar ══════════════════════════════════════════════════════ */
#createNewBar {
    display:none;
    background:#fefce8; border-bottom:1px solid #fde047; padding:14px 24px;
}

/* ══ Info Strip ══════════════════════════════════════════════════════════ */
.info-strip { background:#faf5ff; border-bottom:1px solid #ede9fe; padding:10px 24px; font-size:12px; color:#6d28d9; display:none; }

/* ══ Employee Block ══════════════════════════════════════════════════════ */
.emp-block { border-bottom:1px solid #f1f5f9; padding:18px 24px; }
.emp-block:last-child { border-bottom:none; }
.emp-name-row { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.emp-num { width:26px; height:26px; border-radius:50%; background:#3b82f6; color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0; }
.emp-num.notfound { background:#ef4444; }
.emp-title { font-size:15px; font-weight:700; color:#1e3a8a; }
.badge-found    { background:#dcfce7; color:#166534; border-radius:6px; padding:3px 10px; font-size:11px; font-weight:700; }
.badge-notfound { background:#fee2e2; color:#991b1b; border-radius:6px; padding:3px 10px; font-size:11px; font-weight:700; }
.badge-posisi   { background:#f0f9ff; color:#0369a1; border-radius:6px; padding:3px 10px; font-size:11px; font-weight:600; }

.rec-table { width:100%; font-size:13px; border-collapse:collapse; margin-top:4px; }
.rec-table th { background:#f8fafc; color:#3b82f6; font-weight:700; padding:7px 12px; text-align:center; border-bottom:2px solid #e2e8f0; }
.rec-table td { padding:6px 12px; text-align:center; border-bottom:1px solid #f1f5f9; color:#334155; }
.rec-table tr:hover td { background:#f8fafc; }
.rec-table .no-val { color:#cbd5e1; font-style:italic; }
.day-minggu td { background:#fff1f2 !important; color:#be123c !important; font-weight:600; }

/* ══ Import Result ═══════════════════════════════════════════════════════ */
#importResultSection { display:none; }
.result-header { background:linear-gradient(135deg,#7c3aed,#5b21b6); color:#fff; padding:16px 24px; font-weight:700; font-size:15px; display:flex; align-items:center; gap:10px; }
.result-stat-bar { background:#faf5ff; padding:16px 24px; display:flex; gap:28px; flex-wrap:wrap; border-bottom:1px solid #ede9fe; }
.r-stat { text-align:center; min-width:70px; }
.r-stat .rn { font-size:28px; font-weight:800; }
.r-stat .rl { font-size:10px; font-weight:700; color:#7c3aed; margin-top:2px; letter-spacing:.5px; }
.r-stat.new  .rn { color:#3b82f6; }
.r-stat.ins  .rn { color:#059669; }
.r-stat.upd  .rn { color:#d97706; }
.r-stat.skp  .rn { color:#94a3b8; }
.r-stat.notf .rn { color:#dc2626; }
.r-row { padding:11px 24px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:14px; background:#fff; font-size:13px; }
.r-row:last-child { border-bottom:none; }

/* Theme alignment */
.import-page { color: var(--text); }
.step-bar { margin-bottom:20px; }
.step-pill {
    border-radius:8px !important;
    color:var(--text2) !important;
    background:var(--surface) !important;
    border:1px solid var(--border2) !important;
}
.step-pill .step-num {
    background:var(--surface2) !important;
    color:var(--text2) !important;
}
.step-pill.active {
    background:var(--blue-bg) !important;
    border-color:var(--blue) !important;
    color:var(--blue-text) !important;
}
.step-pill.active .step-num { background:var(--blue) !important; color:#fff !important; }
.step-pill.done {
    background:var(--green-bg) !important;
    border-color:var(--green) !important;
    color:var(--green-text) !important;
}
.step-pill.done .step-num { background:var(--green) !important; color:#fff !important; }
.step-connector { height:1px !important; background:var(--border2) !important; }
.step-connector.done { background:var(--green) !important; }

.import-card {
    background:var(--surface) !important;
    border:1px solid var(--border2) !important;
    border-radius:var(--radius-lg) !important;
    box-shadow:none !important;
    margin-bottom:16px !important;
}
.import-card-header,
.preview-header-bar,
.result-header {
    background:var(--surface) !important;
    color:var(--text) !important;
    border-bottom:1px solid var(--border2) !important;
}
.import-card-header span,
.preview-header-bar h5,
.result-header { color:var(--text) !important; }
.import-card-header i,
.preview-header-bar h5 i,
.result-header i { color:var(--blue) !important; }
.import-card-body { padding:20px !important; }

.template-bar,
.upload-zone,
.result-stat-bar {
    background:var(--surface2) !important;
    border-color:var(--border) !important;
}
.template-bar .tip,
.upload-zone .upload-title,
.emp-title { color:var(--text) !important; }
.template-bar .tip small,
.upload-zone .upload-sub { color:var(--text2) !important; }
.upload-zone:hover,
.upload-zone.dragover {
    background:var(--surface) !important;
    border-color:var(--accent) !important;
    box-shadow:inset 0 0 0 1px var(--accent) !important;
    transform:none !important;
}
.upload-zone .upload-icon { color:var(--blue) !important; }

.btn-template,
.btn-import {
    background:var(--accent) !important;
    border:1px solid var(--accent) !important;
    color:var(--accent-text) !important;
    box-shadow:none !important;
    border-radius:8px !important;
}
.btn-template:hover,
.btn-import:hover {
    color:var(--accent-text) !important;
    opacity:.88;
    transform:none !important;
    box-shadow:none !important;
}
.btn-import:disabled {
    background:var(--surface2) !important;
    border-color:var(--border) !important;
    color:var(--text3) !important;
    opacity:1 !important;
}
.file-badge {
    background:var(--blue-bg) !important;
    color:var(--blue-text) !important;
    border:1px solid rgba(43,98,158,.25);
}
.format-info,
#createNewBar {
    background:var(--amber-bg) !important;
    border-color:rgba(163,97,18,.28) !important;
    color:var(--amber-text) !important;
}
.format-info code,
#createNewBar code {
    background:rgba(163,97,18,.12) !important;
    color:var(--amber-text) !important;
}
#createNewBar [style*="color"] { color:var(--amber-text) !important; }
#createNewBar .custom-control-label { color:var(--text) !important; }
.progress { background:var(--surface2) !important; }
.progress-lbl { color:var(--text2) !important; }
#btnParseHint { color:var(--text3) !important; }
#previewSummary {
    background:var(--surface2) !important;
    color:var(--text2) !important;
    border:1px solid var(--border2);
}
.info-strip {
    background:var(--blue-bg) !important;
    border-bottom:1px solid rgba(43,98,158,.22) !important;
    color:var(--blue-text) !important;
}

.emp-block {
    background:var(--surface) !important;
    border-bottom-color:var(--border2) !important;
}
.emp-num { background:var(--blue) !important; color:#fff !important; }
.emp-num.notfound { background:var(--red) !important; }
.badge-found { background:var(--green-bg) !important; color:var(--green-text) !important; }
.badge-notfound { background:var(--red-bg) !important; color:var(--red-text) !important; }
.badge-posisi { background:var(--blue-bg) !important; color:var(--blue-text) !important; }

.rec-table { color:var(--text) !important; }
.rec-table th {
    background:var(--surface2) !important;
    color:var(--text2) !important;
    border-bottom-color:var(--border2) !important;
}
.rec-table td {
    color:var(--text) !important;
    border-bottom-color:var(--border2) !important;
}
.rec-table tr:hover td { background:var(--surface2) !important; }
.rec-table .no-val { color:var(--text3) !important; }
.day-minggu td { background:var(--red-bg) !important; color:var(--red-text) !important; }

.result-stat-bar { border-bottom-color:var(--border2) !important; }
.r-stat .rn { color:var(--text); }
.r-stat .rl { color:var(--text2) !important; }
.r-stat.new .rn { color:var(--blue) !important; }
.r-stat.ins .rn { color:var(--green) !important; }
.r-stat.upd .rn { color:var(--amber) !important; }
.r-stat.skp .rn { color:var(--text3) !important; }
.r-stat.notf .rn { color:var(--red) !important; }
.r-row {
    background:var(--surface) !important;
    color:var(--text) !important;
    border-bottom-color:var(--border2) !important;
}
.r-row span[style*="background:#dbeafe"] {
    background:var(--blue-bg) !important;
    color:var(--blue-text) !important;
}
.r-row span[style*="color:#64748b"] { color:var(--text2) !important; }
#resultBody,
#resultBody[style] { background:var(--surface) !important; }
#importResultSection .import-card > div:last-child {
    background:var(--surface2) !important;
    border-top:1px solid var(--border2) !important;
}

@media (max-width: 767.98px) {
    .step-bar { align-items:stretch; flex-direction:column; gap:8px; }
    .step-connector { display:none; }
    .step-pill { width:100%; }
    .preview-header-bar,
    .result-header,
    .import-card-body,
    .emp-block,
    .result-stat-bar,
    .r-row { padding-left:16px !important; padding-right:16px !important; }
}
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">{{ __('app.import_attendance_data') }}</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
        <li class="breadcrumb-item active">{{ __('app.import_csv') }}</li>
    </ol>
</div>
@endsection

@section('content')
<div class="import-page">

    {{-- ── Step Indicator ─────────────────────────────────────────────── --}}
    <div class="step-bar">
        <div class="step-pill active" id="sp1"><div class="step-num">1</div><span>{{ __('app.upload_csv') }}</span></div>
        <div class="step-connector" id="sc1"></div>
        <div class="step-pill" id="sp2"><div class="step-num">2</div><span>{{ __('app.review_preview') }}</span></div>
        <div class="step-connector" id="sc2"></div>
        <div class="step-pill" id="sp3"><div class="step-num">3</div><span>{{ __('app.confirm_import') }}</span></div>
    </div>

    {{-- ── STEP 1: Upload ──────────────────────────────────────────────── --}}
    <div class="import-card" id="stepUploadCard">
        <div class="import-card-header" style="background:linear-gradient(135deg,#eff6ff,#f0f9ff);">
            <i class="mdi mdi-file-upload-outline" style="font-size:22px;color:#3b82f6;"></i>
            <span style="color:#1e3a8a;">{{ __('app.upload_csv_file') }}</span>
        </div>
        <div class="import-card-body">

            {{-- Template download --}}
            <div class="template-bar">
                <div class="tip">
                    <i class="mdi mdi-information-outline mr-1"></i>{{ __('app.need_template') }}
                    <small>{{ __('app.template_help') }}</small>
                </div>
                <a href="{{ route('scanlog.template') }}" class="btn-template" id="btnDownloadTemplate">
                    <i class="mdi mdi-download"></i> {{ __('app.download_template') }}
                </a>
            </div>

            {{-- Upload Zone --}}
            <div class="upload-zone" id="uploadZone">
                <i class="mdi mdi-file-delimited upload-icon"></i>
                <div class="upload-title">{{ __('app.drag_drop_csv') }}</div>
                <div class="upload-sub">{{ __('app.or_click_to_select') }}</div>
                <div id="fileNameDisplay"></div>
                <input type="file" id="csv_file" accept=".csv,.txt">
            </div>

            {{-- Format Info --}}
            <div class="format-info mt-3">
                <strong>📋 Format CSV yang diterima:</strong><br>
                Kolom (separator <code>;</code> atau <code>,</code>):
                <code>nama</code> · <code>posisi</code> · <code>tanggal</code> · <code>scan_masuk</code> · <code>scan_keluar</code><br>
                Format tanggal: <code>2026-04-01</code> atau <code>01/04/2026</code> · Format waktu: <code>07:30:00</code> atau <code>07:30</code><br>
                Kolom <code>posisi</code> dan <code>scan_keluar</code> boleh dikosongkan.
                Kolom <code>posisi</code> digunakan untuk membuat karyawan baru jika belum ada di database.
            </div>

            {{-- Progress --}}
            <div id="progressSection">
                <div class="progress-lbl" id="progressLabel">{{ __('app.reading_validating_csv') }}</div>
                <div class="progress" style="border-radius:10px;height:10px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width:0%"></div>
                </div>
            </div>

            {{-- Error --}}
            <div class="alrt alert-danger" id="alertError">
                <i class="mdi mdi-alert-circle mr-2"></i>
                <span id="errorMsg"></span>
            </div>

            {{-- Submit --}}
            <div class="mt-3" style="display:flex;align-items:center;gap:12px;">
                <button id="btnParse" class="btn btn-primary" style="border-radius:9px;font-weight:700;padding:10px 28px;" disabled>
                    <i class="mdi mdi-table-search mr-1"></i> {{ __('app.read_preview_csv') }}
                </button>
                <span id="btnParseHint" style="font-size:12px;color:#94a3b8;">{{ __('app.choose_csv_first') }}</span>
            </div>

        </div>
    </div>

    {{-- ── STEP 2: Preview ─────────────────────────────────────────────── --}}
    <div id="previewSection">
        <div class="import-card">
            <div class="preview-header-bar">
                <h5><i class="mdi mdi-table-eye mr-2"></i>{{ __('app.preview_csv_data') }}</h5>
                <div class="preview-actions">
                    <span id="previewSummary" class="badge badge-light" style="font-size:13px;color:#1e3a8a;padding:6px 14px;"></span>
                    <button class="btn-import" id="btnImport" disabled>
                        <i class="mdi mdi-database-import" style="font-size:17px;"></i>
                        {{ __('app.import_to_database') }}
                    </button>
                </div>
            </div>

            {{-- Buat Karyawan Baru toggle (muncul otomatis jika ada not_found) --}}
            <div id="createNewBar">
                <div style="display:flex;align-items:flex-start;gap:16px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:240px;">
                        <div style="font-size:13px;font-weight:700;color:#713f12;margin-bottom:4px;">
                            <i class="mdi mdi-account-plus mr-1"></i>
                            {{ __('app.create_new_employees_notice') }}
                        </div>
                        <div style="font-size:12px;color:#92400e;">
                            {{ __('app.create_new_employees_help') }}
                            Isi kolom <code>posisi</code> di CSV agar jabatan terisi — default: <em>Karyawan</em>.
                        </div>
                    </div>
                    <div style="flex-shrink:0;padding-top:4px;">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="chkCreateNew">
                            <label class="custom-control-label" for="chkCreateNew"
                                style="font-size:13px;font-weight:700;color:#1e40af;cursor:pointer;padding-top:2px;">
                                {{ __('app.create_new_employees_toggle') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-strip" id="infoStrip">
                <i class="mdi mdi-information-outline mr-1"></i>
                <span id="infoStripText"></span>
            </div>
            <div id="previewBody"></div>
        </div>
    </div>

    {{-- ── STEP 3: Import Result ────────────────────────────────────────── --}}
    <div id="importResultSection">
        <div class="import-card">
            <div class="result-header">
                <i class="mdi mdi-database-check" style="font-size:22px;"></i>
                {{ __('app.import_result') }}
            </div>
            <div class="result-stat-bar">
                <div class="r-stat new">
                    <div class="rn" id="rNew">0</div>
                    <div class="rl">{{ __('app.employees_created_short') }}</div>
                </div>
                <div class="r-stat ins">
                    <div class="rn" id="rIns">0</div>
                    <div class="rl">{{ __('app.inserted_short') }}</div>
                </div>
                <div class="r-stat upd">
                    <div class="rn" id="rUpd">0</div>
                    <div class="rl">{{ __('app.updated_short') }}</div>
                </div>
                <div class="r-stat skp">
                    <div class="rn" id="rSkp">0</div>
                    <div class="rl">{{ __('app.skipped_short') }}</div>
                </div>
                <div class="r-stat notf">
                    <div class="rn" id="rNotf">0</div>
                    <div class="rl">{{ __('app.not_found_short') }}</div>
                </div>
            </div>
            <div id="resultBody" style="background:#fff;"></div>
            <div style="padding:16px 24px;border-top:1px solid #f1f5f9;background:#f8fafc;">
                <button id="btnNewImport" class="btn btn-outline-primary btn-sm" style="border-radius:8px;font-weight:700;">
                    <i class="mdi mdi-upload mr-1"></i> {{ __('app.new_import') }}
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@section('script-bottom')
<script>
$(function () {
    var currentEmployees = null;
    var fileInput  = document.getElementById('csv_file');
    var uploadZone = document.getElementById('uploadZone');
    var tr = {
        chooseCsvFirst: @json(__('app.choose_csv_first')),
        readingCsv: @json(__('app.reading_processing_csv')),
        csvOnly: @json(__('app.csv_only_allowed')),
        noCsvData: @json(__('app.no_csv_data_read')),
        csvFailed: @json(__('app.csv_process_failed')),
        importToDb: @json(__('app.import_to_database')),
        importing: @json(__('app.importing')),
        importFailed: @json(__('app.import_failed')),
        foundInDb: @json(__('app.found_in_db')),
        notRegistered: @json(__('app.not_registered')),
        records: @json(__('app.records')),
        day: @json(__('app.day')),
        date: @json(__('app.date')),
        employeesCount: @json(__('app.employees_count')),
        scanIn: @json(__('app.scan_in_label')),
        scanOut: @json(__('app.scan_out_label')),
        notFoundDb: @json(__('app.not_found_in_database')),
        newEmployee: @json(__('app.new_employee')),
        added: @json(__('app.added')),
        updated: @json(__('app.updated')),
        skipped: @json(__('app.skipped')),
        importConfirmTitle: @json(__('app.import_confirm_title')),
        registeredEmployees: @json(__('app.registered_employees')),
        newEmployeesCreated: @json(__('app.new_employees_will_be_created')),
        unregisteredSkipped: @json(__('app.unregistered_employees_skipped')),
        duplicatesSkipped: @json(__('app.duplicate_records_skipped')),
        continueQuestion: @json(__('app.continue_question')),
        createNewHelp: @json(__('app.create_new_employees_help'))
    };

    $('.format-info').html(
        '<strong>' + @json(__('app.accepted_csv_format')) + '</strong><br>' +
        @json(__('app.csv_columns_help')) + ' (separator <code>;</code> atau <code>,</code>): ' +
        '<code>nama</code> - <code>posisi</code> - <code>tanggal</code> - <code>scan_masuk</code> - <code>scan_keluar</code><br>' +
        @json(__('app.csv_date_format_help')) + ': <code>2026-04-01</code> atau <code>01/04/2026</code> - ' +
        @json(__('app.csv_time_format_help')) + ': <code>07:30:00</code> atau <code>07:30</code><br>' +
        @json(__('app.csv_optional_columns_help')) + ' ' + @json(__('app.csv_position_usage_help'))
    );

    // ── Step helper ──────────────────────────────────────────────────────
    function setStep(n) {
        for (var i = 1; i <= 3; i++) {
            var pill = document.getElementById('sp' + i);
            pill.classList.remove('active','done');
            if (i < n)  pill.classList.add('done');
            if (i === n) pill.classList.add('active');
        }
        for (var j = 1; j <= 2; j++) {
            document.getElementById('sc' + j).classList.toggle('done', j < n);
        }
    }

    // ── Drag & Drop ──────────────────────────────────────────────────────
    uploadZone.addEventListener('click', function () { fileInput.click(); });
    uploadZone.addEventListener('dragover', function (e) { e.preventDefault(); uploadZone.classList.add('dragover'); });
    uploadZone.addEventListener('dragleave', function () { uploadZone.classList.remove('dragover'); });
    uploadZone.addEventListener('drop', function (e) {
        e.preventDefault(); uploadZone.classList.remove('dragover');
        var f = e.dataTransfer.files[0];
        if (f && (f.name.endsWith('.csv') || f.name.endsWith('.txt'))) {
            setFile(f);
        } else {
            showError(tr.csvOnly);
        }
    });
    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) setFile(this.files[0]);
    });

    function setFile(file) {
        var sizeMB = (file.size / 1024 / 1024).toFixed(2);
        $('#fileNameDisplay').html(
            '<div class="file-badge"><i class="mdi mdi-file-delimited mr-1"></i>' +
            escHtml(file.name) + ' (' + sizeMB + ' MB)</div>'
        );
        $('#btnParse').prop('disabled', false);
        $('#btnParseHint').text('');
        hideError();
        $('#previewSection').hide();
        $('#importResultSection').hide();
        currentEmployees = null;
        setStep(1);
    }

    // ── Parse CSV ────────────────────────────────────────────────────────
    $('#btnParse').on('click', function () {
        if (!fileInput.files || !fileInput.files[0]) { showError(tr.chooseCsvFirst); return; }
        hideError();
        $('#previewSection').hide();
        $('#importResultSection').hide();
        showProgress(tr.readingCsv, 40);

        var fd = new FormData();
        fd.append('csv_file', fileInput.files[0]);
        fd.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("scanlog.parse.csv") }}',
            method: 'POST', data: fd, processData: false, contentType: false, timeout: 30000,
            success: function (res) {
                hideProgress();
                if (res.success && res.employees && res.employees.length > 0) {
                    currentEmployees = res.employees;
                    renderPreview(res);
                    setStep(2);
                } else {
                    showError(res.message || tr.noCsvData);
                }
            },
            error: function (xhr) {
                hideProgress();
                var msg = tr.csvFailed;
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showError(msg);
            }
        });
    });

    // ── Render Preview ───────────────────────────────────────────────────
    function renderPreview(res) {
        var employees = res.employees;
        var hariNames = ['MINGGU','SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU'];
        var html = '';

        employees.forEach(function (emp, i) {
            var isFound = emp.found_in_db === true;
            var dbBadge = isFound
                ? '<span class="badge-found"><i class="mdi mdi-check-circle mr-1"></i>' + tr.foundInDb +
                  (emp.db_name && emp.db_name !== emp.nama ? ': ' + escHtml(emp.db_name) : '') + '</span>'
                : '<span class="badge-notfound"><i class="mdi mdi-account-plus mr-1"></i>' + tr.notRegistered + '</span>';

            var posisiBadge = (emp.posisi)
                ? '<span class="badge-posisi"><i class="mdi mdi-briefcase-outline mr-1"></i>' + escHtml(emp.posisi) + '</span>'
                : '';

            html += '<div class="emp-block" style="' + (!isFound ? 'background:#fff8f8;' : '') + '">';
            html += '<div class="emp-name-row">';
            html += '<div class="emp-num' + (!isFound ? ' notfound' : '') + '">' + (i + 1) + '</div>';
            html += '<span class="emp-title">' + escHtml(emp.nama) + '</span>';
            html += dbBadge;
            if (posisiBadge) html += posisiBadge;
            html += '<span style="color:#94a3b8;font-size:12px;margin-left:auto;">' + (emp.records||[]).length + ' ' + tr.records + '</span>';
            html += '</div>';

            html += '<div class="table-responsive">';
            html += '<table class="rec-table"><thead><tr>';
            html += '<th>' + tr.day + '</th><th>' + tr.date + '</th><th>' + tr.scanIn + '</th><th>' + tr.scanOut + '</th>';
            html += '</tr></thead><tbody>';

            (emp.records || []).forEach(function (rec) {
                var d = rec.tanggal ? new Date(rec.tanggal + 'T00:00:00') : null;
                var hari = d ? hariNames[d.getDay()] : '-';
                var isMinggu = d && d.getDay() === 0;
                var tgl = rec.tanggal || '-';
                if (rec.tanggal && rec.tanggal.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    var p = rec.tanggal.split('-');
                    tgl = p[2] + '/' + p[1] + '/' + p[0];
                }
                html += '<tr class="' + (isMinggu ? 'day-minggu' : '') + '">';
                html += '<td>' + hari + '</td>';
                html += '<td>' + tgl + '</td>';
                html += '<td>' + (rec.scan1 || '<span class="no-val">—</span>') + '</td>';
                html += '<td>' + (rec.scan2 || '<span class="no-val">—</span>') + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            html += '</div>';
        });

        $('#previewBody').html(html);
        $('#previewSummary').text(employees.length + ' ' + tr.employeesCount + ' - ' + res.total_rows + ' ' + tr.records);
        $('#previewSummary').text(employees.length + ' karyawan · ' + res.total_rows + ' record');

        $('#previewSummary').text(employees.length + ' ' + tr.employeesCount + ' - ' + res.total_rows + ' ' + tr.records);

        if (res.not_found_count > 0) {
            $('#createNewBar').show();
            $('#infoStripText').html(
                '<strong>' + res.found_count + '</strong> ' + tr.registeredEmployees + ' ' +
                '<strong style="color:#dc2626;">' + res.not_found_count + '</strong> karyawan belum terdaftar — ' +
                tr.createNewHelp
            );
            $('#infoStripText').html('<strong>' + res.found_count + '</strong> ' + tr.registeredEmployees + ' <strong style="color:#dc2626;">' + res.not_found_count + '</strong> ' + tr.notRegistered + ' - ' + tr.createNewHelp);
            $('#infoStrip').show();
        } else {
            $('#createNewBar').hide();
            $('#infoStrip').hide();
        }

        $('#btnImport').prop('disabled', false);
        $('#previewSection').fadeIn(300);
        $('html,body').animate({ scrollTop: $('#previewSection').offset().top - 20 }, 500);
    }

    // ── Import ke Database ───────────────────────────────────────────────
    $('#btnImport').on('click', function () {
        if (!currentEmployees) return;

        var createNew  = $('#chkCreateNew').is(':checked');
        var foundCount = currentEmployees.filter(function (e) { return e.found_in_db; }).length;
        var newCount   = currentEmployees.filter(function (e) { return !e.found_in_db; }).length;

        var confirmMsg = tr.importConfirmTitle + '\n\n';
        if (foundCount > 0) confirmMsg += foundCount + ' ' + tr.registeredEmployees + '\n';
        if (createNew && newCount > 0) {
            confirmMsg += newCount + ' ' + tr.newEmployeesCreated + '\n';
        } else if (!createNew && newCount > 0) {
            confirmMsg += newCount + ' ' + tr.unregisteredSkipped + '\n';
        }
        confirmMsg += '\n' + tr.duplicatesSkipped + '\n' + tr.continueQuestion;

        if (!confirm(confirmMsg)) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mr-1"></i> ' + tr.importing);
        $('#importResultSection').hide();
        setStep(3);

        $.ajax({
            url: '{{ route("scanlog.import.db") }}',
            method: 'POST',
            data: {
                _token:           '{{ csrf_token() }}',
                data:             JSON.stringify(currentEmployees),
                create_employees: createNew ? 1 : 0
            },
            success: function (res) {
                $btn.prop('disabled', false).html('<i class="mdi mdi-database-import"></i> ' + tr.importToDb);
                if (res.success) {
                    renderResult(res);
                } else {
                    showError(res.message || tr.importFailed);
                    setStep(2);
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="mdi mdi-database-import"></i> ' + tr.importToDb);
                var msg = tr.importFailed;
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showError(msg);
                setStep(2);
            }
        });
    });

    // ── Render Result ────────────────────────────────────────────────────
    function renderResult(res) {
        var s = res.summary || {};
        $('#rNew').text(s.emp_created || 0);
        $('#rIns').text(s.inserted   || 0);
        $('#rUpd').text(s.updated    || 0);
        $('#rSkp').text(s.skipped    || 0);
        $('#rNotf').text(s.not_found || 0);

        var html = '';
        (res.details || []).forEach(function (d) {
            var icon, color, info, extra = '';
            if (d.status === 'not_found') {
                icon = 'mdi-account-remove'; color = '#dc2626';
                info = tr.notFoundDb;
            } else {
                icon  = 'mdi-account-check'; color = '#059669';
                info  = '<span style="color:#059669;font-weight:700;">' + (d.inserted||0) + ' ' + tr.added + '</span> · ';
                info += '<span style="color:#d97706;font-weight:700;">' + (d.updated||0)  + ' ' + tr.updated + '</span> · ';
                info += '<span style="color:#94a3b8;">'                 + (d.skipped||0)  + ' ' + tr.skipped + '</span>';
                if (d.newly_created) {
                    extra = '<span style="background:#dbeafe;color:#1d4ed8;border-radius:5px;padding:2px 8px;font-size:11px;font-weight:700;margin-left:4px;">' + tr.newEmployee + '</span>';
                }
            }
            html += '<div class="r-row">';
            html += '<i class="mdi ' + icon + '" style="font-size:20px;color:' + color + ';flex-shrink:0;"></i>';
            html += '<span style="font-weight:700;min-width:180px;">' + escHtml(d.nama) + '</span>';
            html += extra;
            if (d.db_name && d.db_name !== d.nama) {
                html += '<span style="color:#64748b;font-size:12px;">→ ' + escHtml(d.db_name) + '</span>';
            }
            html += '<span style="margin-left:auto;font-size:13px;">' + info + '</span>';
            html += '</div>';
        });

        $('#resultBody').html(html);
        $('#importResultSection').fadeIn(300);
        $('html,body').animate({ scrollTop: $('#importResultSection').offset().top - 20 }, 500);
    }

    // ── Reset ────────────────────────────────────────────────────────────
    $('#btnNewImport').on('click', function () {
        currentEmployees = null;
        $('#csv_file').val('');
        $('#fileNameDisplay').html('');
        $('#btnParse').prop('disabled', true);
        $('#btnParseHint').text(tr.chooseCsvFirst);
        $('#previewSection').hide();
        $('#importResultSection').hide();
        $('#chkCreateNew').prop('checked', false);
        hideError();
        setStep(1);
        $('html,body').animate({ scrollTop: 0 }, 400);
    });

    // ── Helpers ──────────────────────────────────────────────────────────
    function showProgress(msg, pct) {
        $('#progressSection').show();
        $('#progressLabel').text(msg);
        $('#progressBar').css('width', pct + '%');
    }
    function hideProgress() { $('#progressSection').hide(); }
    function showError(msg) { $('#errorMsg').text(msg); $('#alertError').fadeIn(200); }
    function hideError()    { $('#alertError').hide(); }
    function escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});
</script>
@endsection
