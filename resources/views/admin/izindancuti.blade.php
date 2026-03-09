@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet" type="text/css" media="screen">
    <link href="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
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
@include('includes.flash')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    {{-- Filter Bar --}}
                    <div class="d-flex flex-wrap mb-3" style="gap:10px">
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
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>&nbsp;</label>
                            <button id="btnReset" class="btn btn-secondary d-block">Reset</button>
                        </div>
                    </div>

                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                            <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th data-priority="1">Date</th>
                                        <th data-priority="2">Employee ID</th>
                                        <th data-priority="3">Name</th>
                                        <th data-priority="4">Reason</th>
                                        <th data-priority="5">Note</th>
                                        <th data-priority="6">Action</th>
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
                                            <button class="btn btn-danger btn-sm btn-delete"
                                                data-id="{{ $izindancuti->id }}"
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

    {{-- Modal Add Izin & Cuti --}}
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

@section('script')
@endsection

@section('script-bottom')
<script src="{{ URL::asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/dataTables.buttons.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/buttons.html5.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/buttons.print.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ URL::asset('plugins/RWD-Table-Patterns/dist/js/rwd-table.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/pdfmake.min.js') }}"></script>
<script src="{{ URL::asset('plugins/datatables/vfs_fonts.js') }}"></script>
<script>
$(function() {
    $('.table-responsive').responsiveTable({ addDisplayAllBtn: 'btn btn-secondary' });

    var table = $('#datatable-buttons').DataTable({
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
    });

    // Filter bulan & tahun
    $.fn.dataTable.ext.search.push(function(settings, data) {
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
});

// Sync nama karyawan ke hidden emp_id
    $('#izindancutiEmpName').on('input change', function() {
        var val = $(this).val();
        var match = $('#employeeList option').filter(function() {
            return $(this).val() === val;
        });
        if (match.length) {
            $('#izindancutiEmpId').val(match.attr('data-id'));
        } else {
            $('#izindancutiEmpId').val('');
        }
    });

function saveIzinDanCuti() {
    var empId  = $('#izindancutiEmpId').val();
    var date   = $('#izindancutiDate').val();
    var reason = $('#izindancutiReason').val();
    var note   = $('#izindancutiNote').val();

    if (!empId || !date) {
        alert('Karyawan dan tanggal wajib diisi!');
        return;
    }

    $.post('/izindancuti/store', {
        _token:  '{{ csrf_token() }}',
        emp_id:  empId,
        date:    date,
        reason:  reason,
        note:    note,
    }, function(res) {
        if (res.success) {
            $('#addIzinDanCutiModalDanCutiModal').modal('hide');
            location.reload();
        } else {
            alert(res.message || 'Gagal menyimpan');
        }
    }).fail(function() {
        alert('Terjadi kesalahan, coba lagi');
    });
}

function deleteIzinDanCuti(id) {
    if (!confirm('Hapus data izin/cuti ini?')) return;
    $.ajax({
        url: '/izindancuti/delete',
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}', id: id },
        success: function(res) {
            if (res.success) location.reload();
            else alert(res.message || 'Gagal menghapus');
        }
    });
}
</script>
@endsection