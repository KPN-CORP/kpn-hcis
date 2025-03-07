@extends('layouts_.vertical', ['page_title' => 'Approval Cash Advanced'])

@section('css')
<style>
    th {
        color: white !important;
        text-align: center;
    }

    .table {
        border-collapse: separate;
        width: 100%;
        position: relative;
        overflow: auto;
    }

    .table thead th {
        position: -webkit-sticky !important;
        /* For Safari */
        position: sticky !important;
        top: 0 !important;
        z-index: 2 !important;
        background-color: #AB2F2B !important;
        border-bottom: 2px solid #ddd !important;
        padding-right: 6px;
        /* box-shadow: inset 2px 0 0 #fff; */
    }

    .table tbody td {
        background-color: #fff !important;
        padding-right: 10px;
        position: relative;
    }

    .table th.sticky-col-header {
        position: -webkit-sticky !important;
        /* For Safari */
        position: sticky !important;
        left: 0 !important;
        z-index: 3 !important;
        background-color: #ab2f2b !important;
        border-right: 2px solid #AB2F2B !important;
        padding-right: 10px;
        box-shadow: inset 2px 0 0 #fff;
    }

    .table td.sticky-col {
        position: -webkit-sticky !important;
        /* For Safari */
        position: sticky !important;
        left: 0 !important;
        z-index: 1 !important;
        background-color: #fff !important;
        border-right: 2px solid #ddd !important;
        padding-right: 10px;
        box-shadow: inset 6px 0 0 #fff;
    }

    .nav-link.active {
        background-color: #007bff; /* Ganti dengan warna yang diinginkan */
        color: #ffffff; /* Ganti dengan warna teks yang diinginkan */
        border-radius: 5px; /* Tambahkan rounded corners jika diinginkan */
    }

    /* Anda juga dapat menambah gaya untuk efek hover ketika aktif */
    .nav-link.active:hover {
        background-color: #0056b3; /* Ganti dengan warna hover yang diinginkan */
    }
</style>
@endsection

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="row">
            <!-- Breadcrumb Navigation -->
            <div class="col-md-6 mt-3">
                <div class="page-title-box d-flex align-items-center">
                    <ol class="breadcrumb mb-0" style="display: flex; align-items: center; padding-left: 0;">
                        <li class="breadcrumb-item" style="font-size: 25px; display: flex; align-items: center;">
                            <a href="/reimbursements" style="text-decoration: none;" class="text-primary">
                                <i class="bi bi-arrow-left"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            {{ $parentLink }}
                        </li>
                        <li class="breadcrumb-item">
                            {{ $link }}
                        </li>
                    </ol>
                </div>
            </div>
            @include('hcis.reimbursements.approval.navigation.navigationAll')
        </div>

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4" style="border-radius: 0px 10px 10px 10px">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="card-title">{{ $link }}</h3>
                            {{-- <div class="input-group" style="width: 30%;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                                </div>
                                <input type="text" name="customsearch" id="customsearch" class="form-control w-  border-dark-subtle border-left-0" placeholder="search.." aria-label="search" aria-describedby="search" >
                            </div> --}}
                        </div>
                        @include('hcis.reimbursements.approval.navigation.navigationApproval')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('hcis.reimbursements.cashadv.navigation.modalCashadv')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>

    <script>  
        function showManagerInfo(managerType, managers) {
            if (managers.length === 0) {
                Swal.fire({
                    title: managerType,
                    text: 'No approval data available.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return;
            }

            let tableContent = `
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8f9fa; text-align: left;">
                            <th style="padding: 8px;">Role</th>
                            <th style="padding: 8px;">Employee ID</th>
                            <th style="padding: 8px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${managers.map(manager => `
                            <tr>
                                <td style="padding: 8px;">${manager.role_name}</td>
                                <td style="padding: 8px;">${manager.employee_id}</td>
                                <td style="padding: 8px;">${getStatusBadge(manager.approval_status)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            Swal.fire({
                title: managerType,
                html: tableContent,
                icon: 'info',
                width: '600px',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        }

        function getStatusBadge(status) {
            let color = {
                'Approved': 'green',
                'Pending': 'orange',
                'Rejected': 'red',
                'Declaration': 'blue',
                'Draft': 'gray'
            }[status] || 'black';

            return `<span style="color: ${color}; font-weight: bold;">${status}</span>`;
        }

    </script>  
@endsection