   <!-- Top Bar Start -->
   <div class="topbar">

<!-- LOGO -->
<div class="topbar-left">
    <a href="/" class="logo">
        <span>
                <h1 style="color: white; ">AMS</h1>
            </span>
        <i>
            <h1>A</h1>
            </i>
    </a>
</div>

<nav class="navbar-custom">
    <ul class="navbar-right d-flex list-inline float-right mb-0">
        <!-- language-->
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                @if(app()->getLocale() == 'id')
                    <img src="/assets/images/flags/indonesia_flag.png" class="mr-2" height="12" alt="" onerror="this.style.display='none'"/> 
                    <span id="current-lang">Indonesia</span>
                @else
                    <img src="/assets/images/flags/us_flag.jpg" class="mr-2" height="12" alt="" onerror="this.style.display='none'"/> 
                    <span id="current-lang">English</span>
                @endif
                <span class="mdi mdi-chevron-down ml-1"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right language-switch">
                <a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">
                    <img src="/assets/images/flags/us_flag.jpg" alt="" height="16" onerror="this.style.display='none'"/>
                    <span> English </span>
                </a>
                <a class="dropdown-item" href="{{ route('lang.switch', 'id') }}">
                    <img src="/assets/images/flags/indonesia_flag.png" alt="" height="16" onerror="this.style.display='none'"/>
                    <span> Indonesia </span>
                </a>
            </div>
        </li>

        <!-- dark mode toggle -->
        <li class="dropdown notification-list">
            <a class="nav-link waves-effect" href="#" id="btn-dark-mode" title="{{ __('app.dark_mode') }}">
                <i class="mdi mdi-weather-night noti-icon"></i>
            </a>
        </li>
        <li class="dropdown notification-list">
            <div class="dropdown notification-list nav-pro-img">
                <a class="dropdown-toggle nav-link arrow-none waves-effect nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <img src="assets/images/profile1.png" alt="{{ __('app.profile') }}" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                    <!-- item-->
                    <a class="dropdown-item" href="#"><i class="mdi mdi-account-circle m-r-5"></i> {{ __('app.profile') }}</a>
            
                    {{-- <a class="dropdown-item d-block" href="#"><span class="badge badge-success float-right">11</span><i class="mdi mdi-settings m-r-5"></i> Settings</a> --}}
                    <a class="dropdown-item" href="#"><i class="mdi mdi-lock-open-outline m-r-5"></i> {{ __('app.lock_screen') }}</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();"><i class="mdi mdi-power text-danger"></i> {{ __('app.logout') }}</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                </div>
            </div>
        </li>

    </ul>

    <ul class="list-inline menu-left mb-0">
        <li class="float-left">
            <button class="button-menu-mobile open-left waves-effect">
                <i class="mdi mdi-menu"></i>
            </button>
        </li>
        {{-- <li class="d-none d-sm-block">
            <div class="dropdown pt-3 d-inline-block">
                <a class="btn btn-light dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Create
                    </a>

                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">Separated link</a>
                </div>
            </div>
        </li> --}}
    </ul>

</nav>

</div>
<!-- Top Bar End -->
