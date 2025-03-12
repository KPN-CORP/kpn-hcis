@extends('layouts_.vertical', ['page_title' => 'Home Trip'])

@section('css')
    <style>
        .dt-length {
            margin-bottom: 20px;
        }

        th {
            color: white !important;
            text-align: center;
        }

        .table {
            border-collapse: separate;
            width: 100%;
            /* position: relative; */
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
            background-color: #AB2F2B !important;
            border-right: 2px solid #ddd !important;
            padding-right: 10px;
            /* box-shadow: inset 2px 0 0 #fff; */
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
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Breadcrumb Section -->
            <div class="col-md-6 mb-2 d-flex align-items-center">
                <ol class="breadcrumb mb-0" style="align-items: center; padding-left: 0;">
                    <li class="breadcrumb-item" style="font-size: 18px;">
                        <a href="/travel">
                            {{ $parentLink }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        {{ $link }}
                    </li>
                </ol>
            </div>
            @include('hcis.reimbursements.businessTrip.modal')

            <!-- Button Section -->
            <div class="col-md-6 mb-2 d-flex justify-content-center justify-content-md-end align-items-center">
                {{-- <a href="{{ route('export.excel') }}" class="btn btn-outline-success rounded-pill btn-action me-1">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Export to Excel
                </a> --}}
                <a href="{{ route('home-trip-form.add') }}" class="btn btn-primary rounded-pill">
                    <i class="bi bi-plus-circle"></i> Add Home Trip
                </a>
            </div>
        </div>
        <div class="row">
            <div class="card shadow-none p-1 py-3 px-2">
                <div class="d-flex justify-content-center">
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                                aria-selected="true">History</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile"
                                aria-selected="false">Plafonds Home Trip</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact"
                                aria-selected="false">Family Data</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                        @include('hcis.reimbursements.homeTrip.table.historyHomeTrip')
                    </div>
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                        @include('hcis.reimbursements.homeTrip.table.plafonHomeTrip')
                    </div>
                    <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                        @include('hcis.reimbursements.homeTrip.table.familyDataHomeTrip')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('refresh'))
        <script>
            // Refresh the page after 1 seconds
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        </script>
    @endif
@endsection
