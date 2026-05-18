      <!-- ========== Left Sidebar Start ========== -->
            <div class="left side-menu">
                <div class="slimscroll-menu" id="remove-scroll">

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">
                        
                        <!-- Left Menu Start -->
                        <ul class="metismenu" id="side-menu">
                            <li class="menu-title">{{ __('app.main') }}</li>
                            <li>
                                <a href="{{route('admin')}}" class="waves-effect {{ request()->is("admin") || request()->is("admin/*") ? "mm active" : "" }}">
                                    <i class="ti-home"></i> <span> {{ __('app.dashboard') }} </span>
                                </a>
                            </li>
                            

                            <li>
                                <a href="javascript:void(0);" class="waves-effect"><i class="ti-user"></i><span> {{ __('app.employees_menu') }} <span class="float-right menu-arrow"><i class="mdi mdi-chevron-right"></i></span> </span></a>
                                <ul class="submenu">
                                    <li>
                                        <a href="/employees" class="waves-effect {{ request()->is("employees") || request()->is("/employees/*") ? "mm active" : "" }}"><i class="dripicons-view-apps"></i><span>{{ __('app.employees_list') }}</span></a>
                                    </li>
                                   
                                </ul>
                            </li>

                            <li class="menu-title">{{ __('app.management') }}</li>

                            <li>
                                <a href="/schedule" class="waves-effect {{ request()->is("schedule") || request()->is("schedule/*") ? "mm active" : "" }}">
                                    <i class="ti-time"></i> <span> {{ __('app.schedule_menu') }} </span>
                                </a>
                            </li>
                            <li>
                                <a href="/check" class="waves-effect {{ request()->is("check") || request()->is("check/*") ? "mm active" : "" }}">
                                    <i class="dripicons-to-do"></i> <span> {{ __('app.attendance_sheet') }} </span>
                                </a>
                            </li>
                            <li>
                                <a href="/sheet-report" class="waves-effect {{ request()->is("sheet-report") || request()->is("sheet-report/*") ? "mm active" : "" }}">
                                    <i class="ti-file"></i> <span> {{ __('app.sheet_report_menu') }} </span>
                                </a>
                            </li>

                            <li>
                                <a href="/attendance" class="waves-effect {{ request()->is("attendance") ? "mm active" : "" }}">
                                    <i class="ti-calendar"></i> <span> {{ __('app.attendance_logs') }} </span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('lateness.index') }}" class="waves-effect {{ request()->is("attendance/lateness") || request()->is("attendance/lateness/*") ? "mm active" : "" }}">
                                    <i class="ti-alarm-clock"></i><span> {{ __('app.lateness_management') }} </span>
                                </a>
                            </li>
                            <li>
                                <a href="/latetime" class="waves-effect {{ request()->is("latetime") || request()->is("latetime/*") ? "mm active" : "" }}">
                                    <i class="ti-alert"></i><span> {{ __('app.late_time_menu') }} </span>
                                </a>
                            </li>
                            <li>
                                <a href="/izindancuti" class="waves-effect {{ request()->is("izindancuti") || request()->is("izindancuti/*") ? "mm active" : "" }}">
                                    <i class="ti-write"></i> <span> {{ __('app.leave_permission_menu') }} </span>
                                </a>
                            </li>
                            <li>
                                <a href="/overtime" class="waves-effect {{ request()->is("overtime") || request()->is("overtime/*") ? "mm active" : "" }}">
                                    <i class="ti-timer"></i> <span> {{ __('app.overtime_menu') }} </span>
                                </a>
                            </li>
                            <li class="menu-title">{{ __('app.tools') }}</li>
                            <li>
                                <a href="{{ route("finger_device.index") }}" class="waves-effect {{ request()->is("finger_device") || request()->is("finger_device/*") ? "mm active" : "" }}">
                                    <i class="fas fa-fingerprint"></i> <span> {{ __('app.biometric_device') }} </span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('scanlog.upload') }}" class="waves-effect {{ request()->is('scanlog-upload') ? 'mm active' : '' }}">
                                    <i class="ti-cloud-up"></i> <span> {{ __('app.upload_scanlog') }} </span>
                                </a>
                            </li>

                        </ul>

                    </div>
                    <!-- Sidebar -->
                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>
            <!-- Left Sidebar End -->
