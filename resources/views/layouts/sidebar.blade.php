      <!-- ========== Left Sidebar Start ========== -->
            <div class="left side-menu">
                <div class="slimscroll-menu" id="remove-scroll">

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">
                        
                        <!-- Left Menu Start -->
                        <ul class="metismenu" id="side-menu">
                            <li class="menu-title">Main</li>
                            <li>
                                <a href="{{route('admin')}}" class="waves-effect {{ request()->is("admin") || request()->is("admin/*") ? "mm active" : "" }}">
                                    <i class="ti-home"></i> <span> Dashboard </span>
                                </a>
                            </li>
                            

                            <li>
                                <a href="javascript:void(0);" class="waves-effect"><i class="ti-user"></i><span> Employees <span class="float-right menu-arrow"><i class="mdi mdi-chevron-right"></i></span> </span></a>
                                <ul class="submenu">
                                    <li>
                                        <a href="/employees" class="waves-effect {{ request()->is("employees") || request()->is("/employees/*") ? "mm active" : "" }}"><i class="dripicons-view-apps"></i><span>Employees List</span></a>
                                    </li>
                                   
                                </ul>
                            </li>

                            <li class="menu-title">Management</li>

                            <li>
                                <a href="/schedule" class="waves-effect {{ request()->is("schedule") || request()->is("schedule/*") ? "mm active" : "" }}">
                                    <i class="ti-time"></i> <span> Schedule </span>
                                </a>
                            </li>
                            <li>
                                <a href="/check" class="waves-effect {{ request()->is("check") || request()->is("check/*") ? "mm active" : "" }}">
                                    <i class="dripicons-to-do"></i> <span> Attendance Sheet </span>
                                </a>
                            </li>
                            <li>
                                <a href="/sheet-report" class="waves-effect {{ request()->is("sheet-report") || request()->is("sheet-report/*") ? "mm active" : "" }}">
                                    <i class="ti-file"></i> <span> Sheet Report </span>
                                </a>
                            </li>

                            <li>
                                <a href="/attendance" class="waves-effect {{ request()->is("attendance") || request()->is("attendance/*") ? "mm active" : "" }}">
                                    <i class="ti-calendar"></i> <span> Attendance Logs </span>
                                </a>
                            </li>
                            <li>
                                <a href="/latetime" class="waves-effect {{ request()->is("latetime") || request()->is("latetime/*") ? "mm active" : "" }}">
                                    <i class="ti-alert"></i><span> Late Time </span>
                                </a>
                            </li>
                            <li>
                                <a href="/izindancuti" class="waves-effect {{ request()->is("izindancuti") || request()->is("izindancuti/*") ? "mm active" : "" }}">
                                    <i class="ti-write"></i> <span> Izin Dan Cuti </span>
                                </a>
                            </li>
                            <li>
                                <a href="/overtime" class="waves-effect {{ request()->is("overtime") || request()->is("overtime/*") ? "mm active" : "" }}">
                                    <i class="ti-timer"></i> <span> Over Time </span>
                                </a>
                            </li>
                            <li class="menu-title">Tools</li>
                            <li>
                                <a href="{{ route("finger_device.index") }}" class="waves-effect {{ request()->is("finger_device") || request()->is("finger_device/*") ? "mm active" : "" }}">
                                    <i class="fas fa-fingerprint"></i> <span> Biometric Device </span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('scanlog.upload') }}" class="waves-effect {{ request()->is('scanlog-upload') ? 'mm active' : '' }}">
                                    <i class="ti-cloud-up"></i> <span> Upload Scanlog </span>
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
