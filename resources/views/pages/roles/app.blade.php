@extends('layouts_.vertical', ['page_title' => 'Roles'])

@section('css')
@endsection

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('roles') }}">{{ $parentLink }}</a></li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-auto">
                <div class="mb-2">
                    <a class="btn btn-outline-primary rounded-pill btn-sm {{ $active=='create' ? 'active':'' }}" href="{{ route('roles.create') }}">Create Role</a>
                </div>
            </div>
            <div class="col-auto">
                <div class="mb-2">
                    <a class="btn btn-outline-primary rounded-pill btn-sm {{ $active=='manage' ? 'active':'' }}" href="{{ route('roles.manage') }}">Manage Role</a>
                </div>
            </div>
            <div class="col-auto">
                <div class="mb-2">
                    <a class="btn btn-outline-primary rounded-pill btn-sm {{ $active=='assign' ? 'active':'' }}" href="{{ route('roles.assign') }}">Assign Users</a>
                </div>
            </div>
        </div>
        <!-- Content Row -->
        @if(session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif
        @yield('subcontent')
        <div id="subContent"></div>
    </div>
@endsection
