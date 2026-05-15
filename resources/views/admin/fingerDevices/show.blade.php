@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">{{ trans('global.show') }} {{ trans('cruds.finger_device.title_singular') }}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('finger_device.index') }}">{{ trans('cruds.finger_device.title') }}</a></li>
            <li class="breadcrumb-item">{{ trans('global.show') }}</li>
        </ol>
    </div>
@endsection

@section('button')
    <a class="btn btn-secondary btn-sm btn-flat" href="{{ route('finger_device.index') }}">
        <i class="mdi mdi-arrow-left mr-2"></i>{{ trans('global.back_to_list') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0">
                            <table class="table table-striped table-bordered mb-0" style="width:100%;font-size:14px;">
                                <tbody>
                                    <tr>
                                        <th style="width:240px;">{{ trans('cruds.finger_device.fields.id') }}</th>
                                        <td>{{ $fingerDevice->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('cruds.finger_device.fields.name') }}</th>
                                        <td>{{ $fingerDevice->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('cruds.finger_device.fields.ip') }}</th>
                                        <td>{{ $fingerDevice->ip }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ trans('cruds.finger_device.fields.serialNumber') }}</th>
                                        <td>{{ $fingerDevice->serialNumber }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
