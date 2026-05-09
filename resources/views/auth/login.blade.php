@extends('layouts.master-blank')

@section('content')

{{-- Style MUST be inside content (not @section('css')) because ams-theme.css loads AFTER @yield('css') in head.blade.php --}}
<style>
    .login-header h4 { color: #f2f2f2 !important; }
    .login-header p  { color: rgba(255,255,255,0.5) !important; }
</style>

    <div class="wrapper-page" style="margin-top: 5%;">
        <div class="card overflow-hidden account-card mx-3" style="border:1px solid rgba(26,25,23,0.06); border-radius:14px; box-shadow:0 8px 30px rgba(0,0,0,0.06);">
            <div class="login-header" style="background:#1A1917; padding:28px 24px 48px; text-align:center; position:relative;">
                <h4 style="font-family:'DM Sans',sans-serif; font-size:20px; font-weight:600; margin-bottom:6px;">Selamat Datang</h4>
                <p style="font-size:13px; margin-bottom:0;">Masuk sebagai Admin</p>
                <a href="{{ route('welcome') }}" class="logo logo-admin" style="position:absolute; left:50%; bottom:-24px; transform:translateX(-50%); width:48px; height:48px; border-radius:50%; background:#FFFFFF; border:3px solid #FFFFFF; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    <span style="font-family:'DM Mono',monospace; font-size:18px; font-weight:600; color:#1A1917;">A</span>
                </a>
            </div>
            <div class="account-card-content" style="padding: 40px 28px 28px;">
                <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group" style="margin-bottom:16px;">
                        <label for="email" style="font-size:12px; font-weight:500; color:#6B6860; margin-bottom:6px; display:block;">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                            placeholder="email@gmail.com"
                            style="border:1px solid rgba(26,25,23,0.1); border-radius:8px; padding:10px 12px; font-size:13px; font-family:'DM Sans',sans-serif;">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom:16px;">
                        <label for="password" style="font-size:12px; font-weight:500; color:#6B6860; margin-bottom:6px; display:block;">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="current-password"
                            style="border:1px solid rgba(26,25,23,0.1); border-radius:8px; padding:10px 12px; font-size:13px; font-family:'DM Sans',sans-serif;">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                   
                    <div class="form-group row" style="margin-top:20px; align-items:center;">
                        <div class="col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }} style="width:14px; height:14px;">
                                <label class="form-check-label" for="remember" style="font-size:12px; color:#6B6860;">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 text-right">
                            <button type="submit" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:500;background:#1A1917;color:#F7F6F3;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;">Log In</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
@endsection
