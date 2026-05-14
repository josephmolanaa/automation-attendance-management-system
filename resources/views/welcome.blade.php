@include('layouts.welcome')

<style>
    .welcome-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 12px 32px !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        background: #1A1917 !important;
        color: #F7F6F3 !important;
        border: none !important;
        text-decoration: none !important;
        cursor: pointer !important;
        font-family: 'DM Sans', sans-serif !important;
        transition: opacity 0.15s !important;
    }
    .welcome-btn:hover {
        opacity: 0.85 !important;
        color: #F7F6F3 !important;
    }
</style>

    <div class="flex-center position-ref full-height">
        <div class="content">
            <div class="title m-b-md">
                <div class="title m-b-md">
                    <div class="clockStyle" id="clock">123</div>
                    <div id="date" class="welcome-date" style="font-size: 20px; text-align: center; margin-top: 10px; font-weight: 500;"></div>
                    @if (Route::has('login'))
                        <div style="text-align:center; margin-top: 28px;">
                            @auth
                            <a href="{{ url('/admin') }}" class="welcome-btn">Dashboard</a>
                            @else
                            <a href="{{ route('login') }}" class="welcome-btn">Login →</a>
                            @endauth
                        </div>
                    @endif
                </div>            
            </div>
        </div>
    </div>
