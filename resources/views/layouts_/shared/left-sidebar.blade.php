<style>
    html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .logo {
        z-index: 1;
        background-color: #ffffff !important;
    }

    html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav-link:hover,
    html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav-item.active .side-nav-link,
    html[data-sidenav-size=condensed]:not([data-layout=topnav]) .wrapper .leftside-menu .side-nav-link.active {
      box-shadow: 2px 2px 20px rgba(0, 0, 0, 0.1);
    }

    .leftside-menu {
        background-color: #ffffff !important;
        border-right: 1px solid #eef2f7;
    }
    .side-nav-link {
        color: #6c757d !important;
        background-color: rgba(255, 255, 255, 1) !important;
    }
    .side-nav-link i {
        color: #6c757d !important;
        background-color: rgba(255, 255, 255, 1) !important;
    }
    .side-nav-title {
        color: #98a6ad !important;
        background-color: rgba(255, 255, 255, 1) !important;
    }
    .side-nav-link:hover,
    .side-nav-item.active .side-nav-link,
    .side-nav-link.active {
        color: #AB2F2B !important;
        background-color: rgba(255, 255, 255, 1) !important;
        font-weight: 600;
    }
    .side-nav-link:hover i,
    .side-nav-item.active .side-nav-link i,
    .side-nav-link.active i {
        color: #AB2F2B !important;
        background-color: rgba(255, 255, 255, 1) !important;
    }
    .side-nav-second-level li a {
        color: #6c757d !important;
        background-color: rgba(255, 255, 255, 1) !important;
    }
    .side-nav-second-level li a:hover,
    .side-nav-second-level li a.active {
        color: #AB2F2B !important;
        background-color: rgba(255, 255, 255, 1) !important;
    }
</style>

<div class="leftside-menu">
    @if(session('system') == 'kpnpm')
    <a href="{{ Url('/') }}" class="logo text-center">
        <span class="logo-lg">
            <img src="{{ asset('images/logo-dark.png')}}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/logo-sm.png')}}" alt="small logo">
        </span>
    </a>
    @else
    <a href="{{ Url('/') }}" class="logo text-center">
        <span class="logo-lg">
            <img src="{{ asset('images/logo-light_hcis_ori.png')}}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/logo-sm_ori.png')}}" alt="small logo">
        </span>
    </a>
    @endif

    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>

    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <ul class="side-nav">

            @if (auth()->check() && (auth()->user()->employee && (strtolower(auth()->user()->employee->group_company) == "property")))

                <li class="side-nav-title">Approval</li>
                <li class="side-nav-item {{ request()->routeIs('taskBox') ? 'active' : '' }}">
                    <a href="{{ route('businessTrip.approval') }}" class="side-nav-link">
                        <i class="ri-task-line"></i>
                        <span> Task Box </span>
                    </a>
                </li>

                <li class="side-nav-title mt-2">Main Menu</li>
                <li class="side-nav-item {{ request()->routeIs('cashAdvance') ? 'active' : '' }}">
                    <a href="{{ route('cashadvanced') }}" class="side-nav-link">
                        <i class="ri-hand-coin-line"></i>
                        <span> Cash Advance </span>
                    </a>
                </li>
                <li class="side-nav-item {{ request()->routeIs('reimbursements') ? 'active' : '' }}">
                    <a href="{{ route('reimbursements') }}" class="side-nav-link">
                        <i class="ri-cash-line"></i>
                        <span> Reimbursement </span>
                    </a>
                </li>
                <li class="side-nav-item {{ request()->routeIs('travel') ? 'active' : '' }}">
                    <a href="{{ route('travel') }}" class="side-nav-link">
                        <i class="ri-flight-takeoff-line"></i>
                        <span> Travel </span>
                    </a>
                </li>

                <li class="side-nav-title mt-2">Report</li>
                @if (auth()->check() && (auth()->user()->can('reportca_hcis') || auth()->user()->can('report_hcis_md')))
                    @can('reportca_hcis')
                        <li class="side-nav-item">
                            <a href="{{ route('cashadvanced.admin') }}" class="side-nav-link">
                                <i class="ri-file-list-3-line"></i>
                                <span> Cash Advance </span>
                            </a>
                        </li>
                    @endcan
                @endif
                @if (auth()->check() && (auth()->user()->can('reportca_hcis') || auth()->user()->can('report_hcis_md')))
                    <li class="side-nav-item">
                        @if (auth()->user()->can('report_hcis_md'))
                            <a data-bs-toggle="collapse" href="#collapseReportReimburse" aria-expanded="false" aria-controls="collapseReportReimburse" class="side-nav-link">
                                <i class="ri-file-list-3-line"></i>
                                <span> Reimbursement </span>
                                <span class="menu-arrow"></span>
                            </a>
                        @endif
                        @can('report_hcis_md')
                            <div class="collapse" id="collapseReportReimburse">
                                <ul class="side-nav-second-level">
                                    <li><a href="{{ route('medical.admin') }}">Medical</a></li>
                                </ul>
                            </div>
                        @endcan
                    </li>
                @endif
                @if (auth()->check() && (auth()->user()->can('report_hcis_bt') || auth()->user()->can('report_hcis_ht') || auth()->user()->can('report_hcis_tkt') || auth()->user()->can('report_hcis_htl')))
                    <li class="side-nav-item">
                        @if (auth()->user()->can('report_hcis_bt') || auth()->user()->can('report_hcis_ht') || auth()->user()->can('report_hcis_tkt') || auth()->user()->can('report_hcis_htl'))
                            <a data-bs-toggle="collapse" href="#collapseReportTravel" aria-expanded="false" aria-controls="collapseReportTravel" class="side-nav-link">
                                <i class="ri-file-list-3-line"></i>
                                <span> Travel </span>
                                <span class="menu-arrow"></span>
                            </a>
                        @endif
                        <div class="collapse" id="collapseReportTravel">
                            <ul class="side-nav-second-level">
                                @can('report_hcis_bt')
                                    <li><a href="{{ route('businessTrip.admin') }}">Business Travel</a></li>
                                @endcan
                                @can('report_hcis_ht')
                                    <li><a href="{{ route('home-trip.admin') }}">Home Trip</a></li>
                                @endcan
                                @can('report_hcis_tkt')
                                    <li><a href="{{ route('ticket.admin') }}">Ticket</a></li>
                                @endcan
                                @can('report_hcis_htl')
                                    <li><a href="{{ route('hotel.admin') }}">Hotel</a></li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif

                <!--<li class="side-nav-item {{ request()->routeIs('adminMenu') ? 'active' : '' }} mt-2">-->
                <!--    <a href="{{ route('adminMenu') }}" class="side-nav-link">-->
                <!--        <i class="ri-settings-4-line"></i>-->
                <!--        <span> Admin Menu </span>-->
                <!--    </a>-->
                <!--</li>-->

            @else

                <li class="side-nav-title">Main Menu</li>
                <li class="side-nav-item {{ request()->routeIs('reimbursements') ? 'active' : '' }}">
                    <a href="{{ route('reimbursements') }}" class="side-nav-link">
                        <i class="ri-cash-line"></i>
                        <span> Reimbursement </span>
                    </a>
                </li>
                <li class="side-nav-item {{ request()->routeIs('travel') ? 'active' : '' }}">
                    <a href="{{ route('travel') }}" class="side-nav-link">
                        <i class="ri-flight-takeoff-line"></i>
                        <span> Travel </span>
                    </a>
                </li>
            @endif

            @if(auth()->check())
            @can('viewdesignation')
            <li class="side-nav-title mt-2">Admin Setting</li>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAdminSettings" aria-expanded="false" aria-controls="sidebarAdminSettings" class="side-nav-link">
                    <i class="ri-user-settings-line"></i>
                    <span> Admin Settings </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarAdminSettings">
                    <ul class="side-nav-second-level">
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="/admin/approval/setting">Approval Setting</a>
                        </li>
                        @can('viewrole')
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="{{ route('roles') }}">Role Setting</a>
                        </li>
                        @endcan
                        @can('viewdesignation')
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="{{ route('designations') }}">Designation</a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan
            @endif
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
