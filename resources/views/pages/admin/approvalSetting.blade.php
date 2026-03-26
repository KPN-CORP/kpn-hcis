@extends('layouts_.vertical', ['page_title' => 'Approval Setting'])

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    th {
        color: white !important;
        text-align: center;
    }

    #dt-length-0 {
        margin-bottom: 10px;
    }

    .table {
        border-collapse: separate;
        width: 100%;
        /* position: relative; */
        overflow: auto;
        table-layout: fixed;
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

    .table th,
    .table td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .table tr.expanded td {
        white-space: normal;
        overflow: visible;
        text-overflow: unset;
    }

    .table td {
        max-height: 20px;
        transition: all 0.3s ease;
    }

    .table tr.expanded td {
        max-height: 500px;
    }

    .table tbody tr {
        cursor: pointer;
    }

    .cell-content {
        transform-origin: top;
        transition: transform 0.25s ease, opacity 0.25s ease;
        display: flex;
        align-items: center;
    }

    .cell-text {
        display: block;
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    tr:not(.expanded) .cell-content {
        transform: scaleY(1);
        opacity: 1;
    }

    tr.expanded .cell-content {
        transform: scaleY(1.05);
    }

    tr.expanded .cell-text {
        white-space: normal;
    }

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
            <form id="approval-setting-form" action="#" method="POST">
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
                        <select class="form-select form-select-sm select2-multiple" name="group_companies[]" multiple data-placeholder="No Restrictions">
                            @foreach ($groupCompanies as $groupCompany)
                              <option value="{{ $groupCompany }}">{{ $groupCompany }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Restrict Company</label>
                        <select class="form-select form-select-sm select2-multiple" name="contribution_level_codes[]" multiple data-placeholder="No Restrictions">
                            @foreach ($companies as $company)
                              <option value="{{ $company->contribution_level_code }}">{{ $company->contribution_level.' ('.$company->contribution_level_code.')' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Restrict Location</label>
                        <select class="form-select form-select-sm select2-multiple" name="work_areas[]" multiple data-placeholder="No Restrictions">
                            @foreach ($locations as $location)
                              <option value="{{ $location->work_area }}">{{ $location->area.' ('.$location->company_name.')' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">HCGA</label>
                        <select class="form-select form-select-sm select2-single" name="hcga_employee_id" data-placeholder="Select HCGA">
                            <option value=""></option>
                            @foreach ($hcgaEmployees as $hcgaEmployee)
                              <option value="{{ $hcgaEmployee->employee_id }}">{{ $hcgaEmployee->fullname.' ('.$hcgaEmployee->group_company.' - '.$hcgaEmployee->company_name.')' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">KTU</label>
                        <select class="form-select form-select-sm select2-single" name="ktu_employee_id" data-placeholder="Select KTU">
                            <option value=""></option>
                            @foreach ($ktuEmployees as $ktuEmployee)
                              <option value="{{ $ktuEmployee->employee_id }}">{{ $ktuEmployee->fullname.' ('.$ktuEmployee->group_company.' - '.$ktuEmployee->company_name.')' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mt-3 text-end">
                        <button id="approval-setting-reset" type="reset" class="btn btn-light">Reset</button>
                        <button id="approval-setting-submit" type="submit" class="btn btn-primary">Save Setting</button>
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
            <table id="approval-setting-table" class="table table-sm table-hover align-middle mb-0 w-100">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-center">No</th>
                        <th class="text-center">Approval Name</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Group Company</th>
                        <th class="text-center">Company</th>
                        <th class="text-center">Location</th>
                        <th class="text-center">HCGA</th>
                        <th class="text-center">KTU</th>
                        <th class="text-center pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvalSettings as $approvalSetting)
                        <tr>
                            <td class="ps-3">
                                <div class="cell-content">
                                    <span class="cell-text">
                                    </span>
                                </div>
                            </td>
                            <td class="fw-semibold">
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->name ?: "-" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->approval_type ?: "-" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->company_names_label ?: "-" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->contribution_levels_label ?: "-" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->work_areas_label ?: "-" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->hcga_employee->fullname ?: "-" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->ktu_employee->fullname ?: "-" }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center pe-3">
                                <div class="cell-content">
                                    <span class="cell-text">
                                        <button class="btn btn-sm btn-outline-kpn me-1"><i class="ri-pencil-line"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
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

        $('#approval-setting-table').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            searching: true,
            columnDefs: [
                { orderable: false, targets: -1 },
                { className: "text-center", width: "80px", targets: 0 },
            ]
        });

        $('#approval-setting-table tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('button').length) return;

            $(this).toggleClass('expanded');
        });

        $('#approval-setting-reset').on('click', async function (event) {
            event.preventDefault();
            $('.select2-single, .select2-multiple').val(null).trigger('change');
            document.getElementById('approval-setting-form').reset();
        });

        $('#approval-setting-submit').on('click', async function (event) {
            event.preventDefault();

            const form = document.getElementById('approval-setting-form');

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);

            const groupCompanies = formData.getAll('group_companies[]');
            const contributionLevelCodes = formData.getAll('contribution_level_codes[]');
            const workAreas = formData.getAll('work_areas[]');

            const groupCompaniesString = groupCompanies.join(',');
            const contributionLevelCodesString = contributionLevelCodes.join(',');
            const workAreasString = workAreas.join(',');

            formData.set('group_companies', groupCompaniesString);
            formData.set('contribution_level_codes', contributionLevelCodesString);
            formData.set('work_areas', workAreasString);

            formData.delete('group_companies[]');
            formData.delete('contribution_level_codes[]');
            formData.delete('work_areas[]');

            try {
                const response = await fetch('/admin/approval/setting/create', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    if (result.errors) {
                        Swal.fire({
                            title: 'Error!',
                            icon: 'error',
                            html: `
                                <div style="text-align:left;">
                                    <p>${result.message || 'Terjadi kesalahan'}:</p>
                                    <ul style="margin:0; padding-left:20px;">
                                        ${Object.values(result.errors).flat().filter(msg => !msg.toLowerCase().includes('field must be')).map(msg => `<li>${msg}</li>`).join('')}
                                    </ul>
                                </div>
                            `
                        });

                        return
                    }

                    Swal.fire({
                        title: 'Error!',
                        icon: 'error',
                        text: result.message || 'Terjadi kesalahan'
                    });

                    return;
                }

                Swal.fire({
                    title: 'Success!',
                    text: 'Data berhasil disimpan',
                    icon: 'success'
                }).then(() => {
                    form.reset();
                    $('.select2-single, .select2-multiple').val(null).trigger('change');
                    location.reload();
                });
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    icon: 'error',
                    text: 'Terjadi kesalahan'
                });
            }
        });
    });
</script>
@endpush
