@include('layouts.welcome')
  
    <div class="flex-center position-ref full-height">
        <div class="content">
            <div class="title m-b-md">
                <div class="title m-b-md">
                    <div class="clockStyle" id="clock">123</div>
                    <div id="date" style="color: #6B6860; font-size: 20px; text-align: center; margin-top: 10px; font-weight: 500;"></div>
                    @if (Route::has('login'))
                        <div style="text-align:center; margin-top: 28px;">
                            @auth
                            <a href="{{ url('/admin') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 28px;border-radius:8px;font-size:14px;font-weight:500;background:#1A1917;color:#F7F6F3;border:none;text-decoration:none;cursor:pointer;">Dashboard</a>
                            @else
                            <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 28px;border-radius:8px;font-size:14px;font-weight:500;background:#1A1917;color:#F7F6F3;border:none;text-decoration:none;cursor:pointer;">Login →</a>
                            @endauth
                        </div>
                    @endif
                </div>            
            </div>
        </div>
    </div>

