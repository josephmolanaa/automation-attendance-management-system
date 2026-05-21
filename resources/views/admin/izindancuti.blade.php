@extends('layouts.master')
@section('css')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    @include('includes.datatable-controls-style')
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">{{ __('app.leave_permission') }}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.leave_permission') }}</a></li>
        </ol>
    </div>
@endsection

@section('button')
    <button class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#addIzinDanCutiModal">
        <i class="mdi mdi-plus mr-2"></i>{{ __('app.add_new') }}
    </button>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Filter Bar --}}
            <div class="management-filter-bar d-flex flex-wrap">
                <div>
                    <label>{{ __('app.month') }}</label>
                    <select id="filterMonth" class="form-control">
                        <option value="">{{ __('app.all_months') }}</option>
                        <option value="01">{{ __('app.january') }}</option><option value="02">{{ __('app.february') }}</option>
                        <option value="03">{{ __('app.march') }}</option><option value="04">{{ __('app.april') }}</option>
                        <option value="05">{{ __('app.may') }}</option><option value="06">{{ __('app.june') }}</option>
                        <option value="07">{{ __('app.july') }}</option><option value="08">{{ __('app.august') }}</option>
                        <option value="09">{{ __('app.september') }}</option><option value="10">{{ __('app.october') }}</option>
                        <option value="11">{{ __('app.november') }}</option><option value="12">{{ __('app.december') }}</option>
                    </select>
                </div>
                <div>
                    <label>{{ __('app.year') }}</label>
                    <select id="filterYear" class="form-control">
                        <option value="">{{ __('app.all_years') }}</option>
                        @foreach(range(date('Y'), 2024) as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>{{ __('app.from_date') }}</label>
                    <input type="date" id="filterDateFrom" class="form-control">
                </div>
                <div>
                    <label>{{ __('app.to_date') }}</label>
                    <input type="date" id="filterDateTo" class="form-control">
                </div>
                <div>
                    <label>&nbsp;</label>
                    <button id="btnReset" class="btn btn-secondary d-block">{{ __('app.reset') }}</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="izindancuti-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.date') }}</th>
                                        <th>{{ __('app.employee_id') }}</th>
                                        <th>{{ __('app.name') }}</th>
                                        <th>{{ __('app.reason') }}</th>
                                        <th>{{ __('app.note') }}</th>
                                        <th>{{ __('app.action') }}</th>
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
                                                $reasonLabels = ['sakit' => __('app.sick'), 'izin' => __('app.permission'), 'cuti' => __('app.leave'), 'dinas' => __('app.outside_duty')];
                                                $color = $reasonColors[$izindancuti->reason] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $color }} badge-pill">
                                                {{ $reasonLabels[$izindancuti->reason] ?? '-' }}
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
                    <h5 class="modal-title"><i class="mdi mdi-calendar-plus mr-2"></i>{{ __('app.add_leave_permission') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('app.employee') }}</label>
                        <input type="text" id="izindancutiEmpName" class="form-control"
                               placeholder="{{ __('app.employee_name_placeholder') }}"
                               list="employeeList" autocomplete="off">
                        <input type="hidden" id="izindancutiEmpId">
                        <datalist id="employeeList">
                            @foreach(\App\Models\Employee::orderBy('name')->get() as $emp)
                                <option data-id="{{ $emp->id }}" value="{{ $emp->name }}">{{ $emp->emp_id }} - {{ $emp->name }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>{{ __('app.date') }}</label>
                        <input type="date" id="izindancutiDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ __('app.reason') }}</label>
                        <select id="izindancutiReason" class="form-control">
                            <option value="sakit">{{ __('app.sick') }}</option>
                            <option value="izin">{{ __('app.permission') }}</option>
                            <option value="cuti">{{ __('app.leave') }}</option>
                            <option value="dinas">{{ __('app.outside_duty') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('app.note') }} <small class="text-muted">({{ __('app.optional') }})</small></label>
                        <textarea id="izindancutiNote" class="form-control" rows="3" placeholder="{{ __('app.note_placeholder_leave') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button class="btn btn-primary" onclick="saveIzinDanCuti()"><i class="mdi mdi-content-save mr-1"></i>{{ __('app.save') }}</button>
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
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, '{{ __('app.all') }}']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lBf>rtip',
        buttons: [
            { extend: 'copy',  text: '<i class="mdi mdi-content-copy mr-1"></i> {{ __('app.copy') }}',  className: 'btn btn-sm btn-secondary' },
            { extend: 'excel', text: '<i class="mdi mdi-file-excel mr-1"></i> Excel',   className: 'btn btn-sm btn-success', title: '{{ __('app.leave_permission') }}' },
            { extend: 'pdf',   text: '<i class="mdi mdi-file-pdf mr-1"></i> PDF',       className: 'btn btn-sm btn-danger',  title: '{{ __('app.leave_permission') }}', orientation: 'landscape' },
        ],
        columnDefs: [
            { targets: 0, width: '105px', className: 'text-center text-nowrap' },
            { targets: 1, width: '85px', className: 'text-center text-nowrap' },
            { targets: 3, width: '90px', className: 'text-center text-nowrap' },
            { targets: 5, width: '75px', className: 'text-center text-nowrap' },
        ],
        autoWidth: false,
        order: [[0, 'desc']],
        language: window.DataTableLang,
    });

    $.fn.dataTable.ext.search.push(function(settings, data) {
        if (settings.nTable.id !== 'izindancuti-table') return true;
        var month    = $('#filterMonth').val();
        var year     = $('#filterYear').val();
        var dateFrom = $('#filterDateFrom').val();
        var dateTo   = $('#filterDateTo').val();
        var date     = data[0];
        if (!date || date === '-') return true;
        var parts = date.split('-');
        if (month && parts[1] !== month) return false;
        if (year  && parts[0] !== year)  return false;
        if (dateFrom && date < dateFrom) return false;
        if (dateTo   && date > dateTo)   return false;
        return true;
    });

    $('#filterMonth, #filterYear, #filterDateFrom, #filterDateTo').on('change', function() {
        table.draw();
    });

    $('#btnReset').on('click', function() {
        $('#filterMonth').val('');
        $('#filterYear').val('{{ date("Y") }}');
        $('#filterDateFrom, #filterDateTo').val('');
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
        swal({ title: '{{ __('app.warning') }}', text: '{{ __('app.employee_date_required') }}', icon: 'warning', button: '{{ __('app.alert.button.ok') }}' });
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
            swal({ title: '{{ __('app.success') }}', text: '{{ __('app.data_saved') }}', icon: 'success', button: true, timer: 2000 })
                .then(function() { location.reload(); });
        } else {
            swal({ title: '{{ __('app.error') }}', text: res.message || '{{ __('app.save_failed') }}', icon: 'error', button: '{{ __('app.alert.button.ok') }}' });
        }
    }).fail(function() {
        swal({ title: '{{ __('app.error') }}', text: '{{ __('app.try_again_error') }}', icon: 'error', button: '{{ __('app.alert.button.ok') }}' });
    });
}

function deleteIzinDanCuti(id) {
    swal({
        title: '{{ __('app.delete_this_data') }}',
        text: '{{ __('app.delete_leave_text') }}',
        icon: 'warning',
        buttons: ['{{ __('app.cancel') }}', '{{ __('app.delete') }}'],
        dangerMode: true,
    }).then(function(confirm) {
        if (!confirm) return;
        $.ajax({
            url: '/izindancuti/delete',
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}', id: id },
            success: function(res) {
                if (res.success) {
                    swal({ title: '{{ __('app.success') }}', text: '{{ __('app.data_deleted') }}', icon: 'success', button: true, timer: 2000 })
                        .then(function() { location.reload(); });
                } else {
                    swal({ title: '{{ __('app.error') }}', text: res.message || '{{ __('app.delete_failed') }}', icon: 'error', button: '{{ __('app.alert.button.ok') }}' });
                }
            }
        });
    });
}
</script>
@endsection
