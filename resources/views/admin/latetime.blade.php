@extends('layouts.master')

@section('css')
    @include('includes.datatable-controls-style')
    <link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('page-title') {{ __('app.late_time') }} @endsection

@section('body')
<body data-sidebar="dark">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    {{-- Filter Bar --}}
                    <div class="d-flex flex-wrap" style="gap:10px; align-items:flex-end; margin-bottom:16px;">
                        <div>
                            <label>{{ __('app.month') }}</label>
                            <select id="filterMonth" class="form-control">
                                <option value="">{{ __('app.all_months') }}</option>
                                <option value="01">{{ __('app.january') }}</option>
                                <option value="02">{{ __('app.february') }}</option>
                                <option value="03">{{ __('app.march') }}</option>
                                <option value="04">{{ __('app.april') }}</option>
                                <option value="05">{{ __('app.may') }}</option>
                                <option value="06">{{ __('app.june') }}</option>
                                <option value="07">{{ __('app.july') }}</option>
                                <option value="08">{{ __('app.august') }}</option>
                                <option value="09">{{ __('app.september') }}</option>
                                <option value="10">{{ __('app.october') }}</option>
                                <option value="11">{{ __('app.november') }}</option>
                                <option value="12">{{ __('app.december') }}</option>
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

                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="latetime-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.date') }}</th>
                                        <th>{{ __('app.employee_id') }}</th>
                                        <th>{{ __('app.name') }}</th>
                                        <th>{{ __('app.late_duration') }}</th>
                                        <th>{{ __('app.time_in') }}</th>
                                        <th>{{ __('app.time_out') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
<script>
$(document).ready(function() {

    var table = $('#latetime-table').DataTable({
        processing: false,
        serverSide: false,
        ajax: {
            url: '/latetime/data',
            data: function(d) {
                d.bulan  = $('#filterMonth').val();
                d.tahun  = $('#filterYear').val();
                d.dari   = $('#filterDateFrom').val();
                d.sampai = $('#filterDateTo').val();
            }
        },
        columns: [
            { data: 'date' },
            { data: 'emp_id' },
            { data: 'name' },
            { data: 'late_duration', orderable: false },
            { data: 'time_in' },
            { data: 'time_out' },
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, '{{ __('app.all') }}']],
        dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',

        language: window.DataTableLang,
    });

    $('#filterMonth, #filterYear, #filterDateFrom, #filterDateTo').on('change', function() {
        table.ajax.reload();
    });

    $('#btnReset').on('click', function() {
        $('#filterMonth').val('');
        $('#filterYear').val('{{ date("Y") }}');
        $('#filterDateFrom, #filterDateTo').val('');
        table.ajax.reload();
    });
});
</script>
@endsection
