@include('layouts.welcome')
  
    <div class="flex-center position-ref full-height">
        @if (Route::has('login'))
        <div class="top-right links">
            @auth
            <a href="{{ url('/admin') }}">Admin</a>
            @else
            <a href="{{ route('login') }}" class="btn btn-primary text-white" style="padding: 8px 24px;">Login</a>
            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="btn btn-outline-primary" style="padding: 8px 24px; margin-left: 10px;">Register</a>
            @endif
            @endauth
        </div>
        @endif

        <div class="content">
            <div class="title m-b-md">
                <div class="title m-b-md">
                    <div class="clockStyle" id="clock">123</div>
                    <div id="date" style="color: var(--text-secondary); font-size: 24px; text-align: center; margin-top: 10px; font-weight: 500;"></div>
                </div>            
            </div>
        </div>
    </div>

