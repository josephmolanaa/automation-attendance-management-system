@extends('layouts.master')

@section('css')
    @include('includes.datatable-controls-style')
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">{{ trans('cruds.finger_device.title') }}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ trans('cruds.finger_device.title') }}</a></li>
        </ol>
    </div>
@endsection

@section('button')
    <div class="d-flex flex-wrap justify-content-end" style="gap:8px;">
        <a class="btn btn-primary btn-sm btn-flat" href="{{ route('finger_device.create') }}">
            <i class="mdi mdi-plus mr-2"></i>{{ trans('global.add') }} {{ trans('cruds.finger_device.title_singular') }}
        </a>
        <a class="btn btn-secondary btn-sm btn-flat" href="{{ route('finger_device.clear.attendance') }}">
            <i class="mdi mdi-broom mr-2"></i>{{ __('app.clear_device_attendance') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table id="finger-device-table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;font-size:14px;">
                                <thead>
                                    <tr>
                                        <th>{{ trans('cruds.finger_device.fields.id') }}</th>
                                        <th>{{ trans('cruds.finger_device.fields.name') }}</th>
                                        <th>{{ trans('cruds.finger_device.fields.ip') }}</th>
                                        <th>{{ trans('cruds.finger_device.fields.serialNumber') }}</th>
                                        <th>{{ __('app.status') }}</th>
                                        <th>{{ __('app.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $helper = new \App\Helpers\FingerHelper(); @endphp
                                    @foreach($devices as $finger_device)
                                        @php $device = $helper->init($finger_device->ip); @endphp
                                        <tr data-entry-id="{{ $finger_device->id }}">
                                            <td>{{ $finger_device->id ?? '' }}</td>
                                            <td>{{ $finger_device->name ?? '' }}</td>
                                            <td>{{ $finger_device->ip ?? '' }}</td>
                                            <td>{{ $finger_device->serialNumber ?? '' }}</td>
                                            <td>
                                                @if($helper->getStatus($device))
                                                    <span class="badge badge-success badge-pill">{{ __('app.active') }}</span>
                                                @else
                                                    <span class="badge badge-danger badge-pill">{{ __('app.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap" style="gap:6px;">
                                                    <a class="btn btn-info btn-sm btn-flat" href="{{ route('finger_device.show', $finger_device->id) }}">
                                                        <i class="mdi mdi-eye mr-1"></i>{{ trans('global.view') }}
                                                    </a>
                                                    <a class="btn btn-success btn-sm btn-flat" href="{{ route('finger_device.edit', $finger_device->id) }}">
                                                        <i class="mdi mdi-pencil mr-1"></i>{{ trans('global.edit') }}
                                                    </a>
                                                    <a class="btn btn-outline-success btn-sm btn-flat" href="{{ route('finger_device.add.employee', $finger_device->id) }}">
                                                        <i class="mdi mdi-account-plus mr-1"></i>{{ __('app.add_employee') }}
                                                    </a>
                                                    <a class="btn btn-outline-primary btn-sm btn-flat" href="{{ route('finger_device.get.attendance', $finger_device->id) }}">
                                                        <i class="mdi mdi-download mr-1"></i>{{ __('app.get_attendance') }}
                                                    </a>
                                                    <form action="{{ route('finger_device.destroy', $finger_device->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}');"
                                                          style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm btn-flat">
                                                            <i class="mdi mdi-delete mr-1"></i>{{ trans('global.delete') }}
                                                        </button>
                                                    </form>
                                                </div>
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
@endsection

@section('script-bottom')
    <script>
        $(function () {
            $('#finger-device-table').DataTable({
                destroy: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, '{{ __('app.all') }}']],
                dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rtip',
                order: [[0, 'asc']],
                language: window.DataTableLang,
            });
        });
    </script>
@endsection
