<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">
    @if(session('system') == 'kpnpm')
    <!-- Brand Logo Light -->
    <a href="{{ Url('/') }}" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{ asset('images/logo.png')}}" alt="logo">
            
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/logo-sm.png')}}" alt="small logo">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="{{ Url('/') }}" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{ asset('images/logo-dark.png')}}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/logo-sm.png')}}" alt="small logo">
        </span>
    </a>
    @else
    <!-- Brand Logo Light -->
    <a href="" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{ asset('images/logo_hcis_ori.png')}}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/logo-sm_ori.png')}}" alt="small logo">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{ asset('images/logo-dark_hcis_ori.png')}}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/logo-sm_ori.png')}}" alt="small logo">
        </span>
    </a>
    @endif
    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <!-- Full Sidebar Menu Close Button -->
    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>

        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-title">Navigation</li>

            {{-- @if (auth()->user()->hasRole('superadmin'))
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarDashboards" aria-expanded="false" aria-controls="sidebarDashboards" class="side-nav-link">
                    <i class="ri-home-4-line"></i>
                    <span> Dashboards </span>
                </a>
                <div class="collapse" id="sidebarDashboards">
                    <ul class="side-nav-second-level">
                        <li>
                            <a href="{{ route('dashboard') }}">Analytics</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif --}}
            
            <li class="side-nav-item">
                <a href="{{ route('reimbursements') }}"  aria-controls="sidebarEmail" class="side-nav-link">
                    <i class="ri-star-line"></i>
                    <span> Reimbursement </span>
                </a>
            </li>
            @if (auth()->user()->hasRole('superadmin'))
            <li class="side-nav-item">
                <a href="{{ route('travel') }}"  aria-controls="sidebarEmail" class="side-nav-link">
                    <i class="ri-star-line"></i>
                    <span> Travel </span>
                </a>
            </li>
            @endif
            
            <li class="side-nav-item">
                <a href="{{ route('docGenerator') }}" class="side-nav-link">
                    <i class="ri-file-text-line"></i>
                    <span> Document </span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ url('/guides') }}" class="side-nav-link">
                    <i class="ri-file-text-line"></i>
                    <span> User Guide </span>
                </a>
            </li>

            @if(auth()->check())
            @can('adminmenu')
            <li class="side-nav-title">Admin</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarCharts" aria-expanded="false" aria-controls="sidebarCharts" class="side-nav-link">
                    <i class="ri-admin-line"></i>
                    <span> Admin Menu </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarCharts">
                    <ul class="side-nav-second-level">
                        @can('viewsetting')
                        <li class="side-nav-item">
                            <a data-bs-toggle="collapse" href="#sidebarApexCharts" aria-expanded="false" aria-controls="sidebarApexCharts">
                                <span> Settings </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarApexCharts">
                                <ul class="side-nav-third-level">
                                    @can('viewschedule')
                                    <li>
                                        <a href="{{ route('schedules') }}">Schedule</a>
                                    </li>
                                    @endcan
                                    @can('viewlayer')
                                    <li>
                                        <a href="{{ route('layer') }}">Layer</a>
                                    </li>
                                    @endcan
                                    @can('viewrole')
                                    <li>
                                        <a href="{{ route('roles') }}">Role</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan
                        @can('viewonbehalf')
                        <li class="side-nav-item">
                            <a  href="{{ route('onbehalf') }}">On Behalfs</a>
                        </li>
                        @endcan
                        @can('viewreport')
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports') }}">Reports</a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>

            @endcan
            @endif

        </ul>
        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
