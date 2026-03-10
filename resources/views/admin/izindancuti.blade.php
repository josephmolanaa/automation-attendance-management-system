@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <style>
        .dataTables_length label,
        .dataTables_filter label,
        .dataTables_length select,
        .dataTables_filter input { font-size: 14px !important; }
        .dataTables_length select {
            height: 36px !important; width: 75px !important; padding: 4px 8px !important;
            background-image: none !important; -webkit-appearance: auto !important; appearance: auto !important;
        }
        .dataTables_filter input {
            height: 36px !important; padding: 4px 10px !important;
            border-radius: 6px !important; border: 1px solid #ced4da !important;
        }
        .dt-buttons { display: flex !important; align-items: center !important; gap: 6px !important; }
        .dt-buttons .btn { height: 38px !important; font-size: 14px !important; display: flex !important; align-items: center !important; }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Izin & Cuti</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Izin & Cuti</a></li>
        </ol>
    </div>
@endsection

@section('button')
    <button class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#addIzinDanCutiModal">
        <i class="mdi mdi-plus mr-2"></i>Add New
    </button>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    {{-- Filter Bar --}}
                    <div class="d-flex flex-wrap" style="gap:10px; align-items:flex-end; margin-bottom:16px;">
                        <div>
                            <label>Bulan</label>
                            <select id="filterMonth" class="form-control">
                                <option value="">Semua Bulan</option>
                                <option value="01">Januari</option><option value="02">Februari</option>
                                <option value="03">Maret</option><option value="04">April</option>
                                <option value="05">Mei</option><option value="06">Juni</option>
                                <option value="07">Juli</option><option value="08">Agustus</option>
                                <option value="09">September</option><option value="10">Oktober</option>
                                <option value="11">November</option><option value="12">Desember</option>
                            </select>
                        </div>
                        <div>
                            <label>Tahun</label>
                            <select id="filterYear" class="form-control">
                                <option value="">Semua Tahun</option>
                                @foreach(range(date('Y'), 2024) as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button id="btnReset" class="btn btn-secondary d-block">Reset</button>
                        </div>
                    </div>

                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="izindancuti-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Reason</th>
                                        <th>Note</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($izinDanCutis as $izindancuti)
                                    <tr>
                                        <td>{{ $izindancuti->izindancuti_date }}</td>
                                        <td>{{ optional($izindancuti->employee)->emp_id ?? $izindancuti->emp_id }}</td>
                                        <td>{{ optional($izindancuti->employee)->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $reasonColors = ['sakit' => 'danger', 'izin' => 'warning', 'cuti' => 'info', 'dinas' => 'primary'];
                                                $color = $reasonColors[$izindancuti->reason] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $color }} badge-pill">
                                                {{ ucfirst($izindancuti->reason ?? '-') }}
                                            </span>
                                        </td>
                                        <td>{{ $izindancuti->note ?? '-' }}</td>
                                        <td>
                                            <button class="btn btn-danger btn-sm"
                                                onclick="deleteIzinDanCuti({{ $izindancuti->id }})">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add --}}
    <div class="modal fade" id="addIzinDanCutiModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-calendar-plus mr-2"></i>Tambah Izin & Cuti</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Karyawan</label>
                        <input type="text" id="izindancutiEmpName" class="form-control"
                               placeholder="Ketik nama karyawan..."
                               list="employeeList" autocomplete="off">
                        <input type="hidden" id="izindancutiEmpId">
                        <datalist id="employeeList">
                            @foreach(\App\Models\Employee::orderBy('name')->get() as $emp)
                                <option data-id="{{ $emp->id }}" value="{{ $emp->name }}">{{ $emp->emp_id }} - {{ $emp->name }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" id="izindancutiDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Alasan</label>
                        <select id="izindancutiReason" class="form-control">
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                            <option value="cuti">Cuti</option>
                            <option value="dinas">Dinas Luar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catatan <small class="text-muted">(opsional)</small></label>
                        <textarea id="izindancutiNote" class="form-control" rows="3" placeholder="contoh: demam tinggi, perlu istirahat"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" onclick="saveIzinDanCuti()"><i class="mdi mdi-content-save mr-1"></i>Simpan</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script-bottom')
<script>
$(function() {
    var table = $('#izindancuti-table').DataTable({
        destroy: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lBf>rtip',
        buttons: [
            { extend: 'copy',  text: '<i class="mdi mdi-content-copy mr-1"></i> Copy',  className: 'btn btn-sm btn-secondary' },
            { extend: 'excel', text: '<i class="mdi mdi-file-excel mr-1"></i> Excel',   className: 'btn btn-sm btn-success', title: 'Izin & Cuti Data' },
            { extend: 'pdf',   text: '<i class="mdi mdi-file-pdf mr-1"></i> PDF',       className: 'btn btn-sm btn-danger',  title: 'Izin & Cuti Data', orientation: 'landscape' },
        ],
        order: [[0, 'desc']],
        language: {
            emptyTable: 'Tidak ada data tersedia',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 data',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            paginate: { next: 'Selanjutnya', previous: 'Sebelumnya' }
        },
    });

    $.fn.dataTable.ext.search.push(function(settings, data) {
        if (settings.nTable.id !== 'izindancuti-table') return true;
        var month = $('#filterMonth').val();
        var year  = $('#filterYear').val();
        var date  = data[0];
        if (!date || date === '-') return true;
        var parts = date.split('-');
        if (month && parts[1] !== month) return false;
        if (year  && parts[0] !== year)  return false;
        return true;
    });

    $('#filterMonth, #filterYear').on('change', function() { table.draw(); });
    $('#btnReset').on('click', function() {
        $('#filterMonth, #filterYear').val('');
        table.draw();
    });

    $('#izindancutiEmpName').on('input change', function() {
        var val = $(this).val();
        var match = $('#employeeList option').filter(function() {
            return $(this).val() === val;
        });
        $('#izindancutiEmpId').val(match.length ? match.attr('data-id') : '');
    });
});

function saveIzinDanCuti() {
    var empId  = $('#izindancutiEmpId').val();
    var date   = $('#izindancutiDate').val();
    var reason = $('#izindancutiReason').val();
    var note   = $('#izindancutiNote').val();

    if (!empId || !date) {
        swal({ title: 'Perhatian', text: 'Karyawan dan tanggal wajib diisi!', icon: 'warning', button: 'OK' });
        return;
    }

    $.post('/izindancuti/store', {
        _token: '{{ csrf_token() }}',
        emp_id: empId,
        date:   date,
        reason: reason,
        note:   note,
    }, function(res) {
        if (res.success) {
            $('#addIzinDanCutiModal').modal('hide');
            swal({ title: 'Berhasil!', text: 'Data berhasil disimpan', icon: 'success', button: true, timer: 2000 })
                .then(function() { location.reload(); });
        } else {
            swal({ title: 'Gagal', text: res.message || 'Gagal menyimpan', icon: 'error', button: 'OK' });
        }
    }).fail(function() {
        swal({ title: 'Error', text: 'Terjadi kesalahan, coba lagi', icon: 'error', button: 'OK' });
    });
}

function deleteIzinDanCuti(id) {
    swal({
        title: 'Hapus data ini?',
        text: 'Data izin/cuti akan dihapus permanen.',
        icon: 'warning',
        buttons: ['Batal', 'Hapus'],
        dangerMode: true,
    }).then(function(confirm) {
        if (!confirm) return;
        $.ajax({
            url: '/izindancuti/delete',
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}', id: id },
            success: function(res) {
                if (res.success) {
                    swal({ title: 'Berhasil!', text: 'Data berhasil dihapus', icon: 'success', button: true, timer: 2000 })
                        .then(function() { location.reload(); });
                } else {
                    swal({ title: 'Gagal', text: res.message || 'Gagal menghapus', icon: 'error', button: 'OK' });
                }
            }
        });
    });
}
</script>
@endsection