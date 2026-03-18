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
        /*background-color: rgba(171, 47, 43, 0.05) !important;*/
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
            <li class="side-nav-title">Menu</li>

            <li class="side-nav-item {{ request()->routeIs('reimbursements') ? 'active' : '' }}">
                <a href="{{ route('reimbursements') }}" aria-controls="sidebarEmail" class="side-nav-link">
                    <i class="ri-refund-2-line"></i>
                    <span> Reimbursement </span>
                </a>
            </li>

            <li class="side-nav-item {{ request()->routeIs('travel') ? 'active' : '' }}">
                <a href="{{ route('travel') }}" aria-controls="sidebarEmail" class="side-nav-link">
                    <i class="ri-plane-line"></i>
                    <span> Travel </span>
                </a>
            </li>

            @if(auth()->check())
            @can('viewdesignation')
            <li class="side-nav-title mt-2">Admin</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAdminSettings" aria-expanded="false" aria-controls="sidebarAdminSettings" class="side-nav-link">
                    <i class="ri-admin-line"></i>
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
