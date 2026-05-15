@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">{{ trans('global.create') }} {{ trans('cruds.finger_device.title_singular') }}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">{{ __('app.breadcrumb_home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('finger_device.index') }}">{{ trans('cruds.finger_device.title') }}</a></li>
            <li class="breadcrumb-item">{{ trans('global.create') }}</li>
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
                    <form method="POST" action="{{ route('finger_device.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="required" for="title">{{ trans('cruds.finger_device.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                   type="text"
                                   name="name"
                                   id="title"
                                   value="{{ old('name', '') }}"
                                   required>
                            @if($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                            <span class="help-block">{{ trans('cruds.finger_device.fields.name_helper') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="required" for="ip">{{ trans('cruds.finger_device.fields.ip') }}</label>
                            <input class="form-control {{ $errors->has('ip') ? 'is-invalid' : '' }}"
                                   type="text"
                                   name="ip"
                                   id="ip"
                                   value="{{ old('ip', '') }}"
                                   required>
                            @if($errors->has('ip'))
                                <span class="text-danger">{{ $errors->first('ip') }}</span>
                            @endif
                            <span class="help-block">{{ trans('cruds.finger_device.fields.ip_helper') }}</span>
                        </div>

                        <div class="form-group mb-0">
                            <button class="btn btn-primary" type="submit">
                                <i class="mdi mdi-content-save mr-1"></i>{{ trans('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
