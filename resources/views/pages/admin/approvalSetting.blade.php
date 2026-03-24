@extends('layouts_.vertical', ['page_title' => 'Approval Setting'])

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .text-kpn { color: #AB2F2B !important; }
    .btn-kpn { background-color: #AB2F2B; color: #ffffff; border: none; }
    .btn-kpn:hover { background-color: #8a2522; color: #ffffff; }
    .btn-outline-kpn { color: #AB2F2B; border-color: #AB2F2B; background: transparent; }
    .btn-outline-kpn:hover { background-color: #AB2F2B; color: #ffffff; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white ">
            <h5 class="mb-0 text-kpn fw-bold">Create Approval Flow</h5>
        </div>
        <div class="card-body pt-0">
            <form action="#" method="POST">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Approval Name</label>
                        <input type="text" class="form-control form-control-sm" name="approval_name" placeholder="e.g., Declaration Approval HO">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Approval Type</label>
                        <input type="text" class="form-control form-control-sm bg-light" name="approval_type" value="Declaration" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Restrict Group Company</label>
                        <select class="form-select form-select-sm select2-multiple" multiple data-placeholder="No Restrictions">
                            @foreach ($groupCompanies as $groupCompany)
                              <option value="{{ $groupCompany }}">{{ $groupCompany }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Restrict Company</label>
                        <select class="form-select form-select-sm select2-multiple" multiple data-placeholder="No Restrictions">
                            @foreach ($companies as $company)
                              <option value="{{ $company->contribution_level_code }}">{{ $company->contribution_level.' ('.$company->contribution_level_code.')' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Restrict Location</label>
                        <select class="form-select form-select-sm select2-multiple" multiple data-placeholder="No Restrictions">
                            @foreach ($locations as $location)
                              <option value="{{ $location->work_area }}">{{ $location->area.' ('.$location->company_name.')' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">HCGA</label>
                        <select class="form-select form-select-sm select2-single" data-placeholder="Select HCGA">
                            <option value=""></option>
                            <option value="Andi Saputra">Andi Saputra</option>
                            <option value="Budi Santoso">Budi Santoso</option>
                            <option value="Citra Lestari">Citra Lestari</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">KTU</label>
                        <select class="form-select form-select-sm select2-single" data-placeholder="Select KTU">
                            <option value=""></option>
                            <option value="Dewi Anggraini">Dewi Anggraini</option>
                            <option value="Eko Prasetyo">Eko Prasetyo</option>
                            <option value="Fajar Setiawan">Fajar Setiawan</option>
                        </select>
                    </div>

                    <div class="col-12 mt-3 text-end">
                        <button type="reset" class="btn btn-light">Reset</button>
                        <button type="submit" class="btn btn-primary">Save Setting</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-kpn fw-bold">Approval Flow List</h5>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table id="approvalTable" class="table table-sm table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Approval Name</th>
                        <th>Type</th>
                        <th>Group Company</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>HCGA</th>
                        <th>KTU</th>
                        <th class="text-center pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-3">1</td>
                        <td class="fw-semibold">Declaration HO</td>
                        <td>Declaration</td>
                        <td>KPN Corp</td>
                        <td>-</td>
                        <td>Jakarta</td>
                        <td>Andi Saputra</td>
                        <td>Dewi Anggraini</td>
                        <td class="text-center pe-3">
                            <button class="btn btn-sm btn-outline-kpn me-1"><i class="ri-pencil-line"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-3">2</td>
                        <td class="fw-semibold">Declaration Site A</td>
                        <td>Declaration</td>
                        <td>KPN Plantation</td>
                        <td>PT Agrindo, PT Bintang</td>
                        <td>Sumatra</td>
                        <td>Budi Santoso</td>
                        <td>Eko Prasetyo</td>
                        <td class="text-center pe-3">
                            <button class="btn btn-sm btn-outline-kpn me-1"><i class="ri-pencil-line"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-3">3</td>
                        <td class="fw-semibold">Declaration All Sites</td>
                       <td>Declaration</td>
                        <td>KPN Plantation, KPN Agribusiness</td>
                        <td>-</td>
                        <td>Sumatra, Kalimantan</td>
                        <td>Citra Lestari</td>
                        <td>Fajar Setiawan</td>
                        <td class="text-center pe-3">
                            <button class="btn btn-sm btn-outline-kpn me-1"><i class="ri-pencil-line"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-3">4</td>
                        <td class="fw-semibold">Declaration Bintang</td>
                        <td>Declaration</td>
                        <td>-</td>
                        <td>PT Bintang</td>
                        <td>-</td>
                        <td>Andi Saputra</td>
                        <td>Fajar Setiawan</td>
                        <td class="text-center pe-3">
                            <button class="btn btn-sm btn-outline-kpn me-1"><i class="ri-pencil-line"></i></button>
                            <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true
        });
        $('.select2-single').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true
        });
    });
</script>
@endpush
