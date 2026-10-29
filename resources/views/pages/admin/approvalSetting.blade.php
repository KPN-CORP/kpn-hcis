@extends('layouts_.vertical', ['page_title' => 'Approval Setting'])

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
{{-- CSS DataTables 1.13.6 untuk tabel DataTables 2.x: nama kelasnya sudah
     berbeda (dataTables_* vs dt-*), jadi tidak ada yang kena. Layout sudah
     memuat vendor/datatables/dataTables.bootstrap5.min.css yang versinya
     cocok.
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
--}}
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

    /* table-layout: fixed memaksa 9 kolom muat di lebar container, jadi
       kolom teksnya tersisa ~40px dan terpotong hampir seluruhnya. Dengan
       min-width, kolomnya dapat ruang wajar dan .table-responsive di
       sekelilingnya yang menggeser ke samping di layar sempit. */
    #approval-setting-table {
        min-width: 1150px;
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

    <div id="approval-setting-form-alert" class="alert alert-warning d-none" role="alert">
        Edit Data
    </div>

    <div id="approval-setting-form-wrapper" class="card border-0 shadow-sm mb-4">
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
                        {{-- Diisi Select2 lewat pencarian ke
                             admin_approval_setting_employees. Versi lama
                             merender SELURUH tabel employees sebagai <option>
                             di sini (dan sekali lagi di KTU di bawah), yang
                             membuat HTML halaman ini ~3,3 MB. --}}
                        <select class="form-select form-select-sm select2-employee" name="hcga_employee_id" data-placeholder="Select HCGA">
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">KTU</label>
                        <select class="form-select form-select-sm select2-employee" name="ktu_employee_id" data-placeholder="Select KTU">
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="col-12 mt-3 text-end">
                        <button id="approval-setting-reset" type="reset" class="btn btn-light">Reset</button>
                        <button id="approval-setting-submit" type="submit" class="btn btn-primary" data-id="">Save Setting</button>
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
                    @foreach ($approvalSettings as $index => $approvalSetting)
                        <tr>
                            <td class="ps-3">
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $index + 1 }}
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
                                        {{ $approvalSetting->company_names_label ?: "ALL" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->contribution_levels_label ?: "ALL" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->work_areas_label ?: "ALL" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->hcga_employee ? $approvalSetting->hcga_employee->fullname ?: "-" : "-" }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="cell-content">
                                    <span class="cell-text">
                                        {{ $approvalSetting->ktu_employee ? $approvalSetting->ktu_employee->fullname ?: "-" : "-" }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center pe-3">
                                <div class="cell-content">
                                    <span class="cell-text">
                                        <button
                                            class="approval-setting-edit btn btn-sm btn-outline-kpn me-1"
                                            data-id="{{ $approvalSetting->id }}"
                                            data-approval_name="{{ $approvalSetting->name }}"
                                            data-approval_type="{{ $approvalSetting->approval_type }}"
                                            data-group_companies="{{ $approvalSetting->company_names }}"
                                            data-contribution_level_codes="{{ $approvalSetting->contribution_level_codes }}"
                                            data-work_areas="{{ $approvalSetting->work_areas }}"
                                            data-hcga_employee_id="{{ $approvalSetting->hcga_employee_id }}"
                                            data-ktu_employee_id="{{ $approvalSetting->ktu_employee_id }}"
                                            data-hcga_employee_label="{{ $approvalSetting->hcga_employee_label }}"
                                            data-ktu_employee_label="{{ $approvalSetting->ktu_employee_label }}"
                                        >
                                                <i class="ri-pencil-line"></i>
                                        </button>
                                        <button class="approval-setting-delete btn btn-sm btn-outline-danger" data-id="{{ $approvalSetting->id }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
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
{{-- jQuery, Bootstrap dan DataTables sudah dimuat layout (head-script.blade
     dan script.blade), termasuk dataTables.bootstrap5.min.js yang menggambar
     paging/length/info bergaya Bootstrap.

     Stack ini dirender PALING AKHIR, jadi memuat ulang DataTables dari CDN di
     sini justru menimpa $.fn.DataTable yang sudah dipatch integrasi Bootstrap
     5 tadi - renderer-nya hilang dan tabelnya keluar tanpa gaya
     (wrapper-nya jadi "dt-container" saja, tanpa "dt-bootstrap5").
     Begitu juga jQuery: memuatnya lagi di sini mengganti instance yang sudah
     dipakai plugin-plugin sebelumnya.

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
--}}
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

        // Dropdown HCGA / KTU: isinya dicari di server, 30 baris sekali ambil.
        $('.select2-employee').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
            placeholder: function () {
                return $(this).data('placeholder');
            },
            minimumInputLength: 0,
            ajax: {
                // URL relatif (absolute: false): di belakang reverse proxy
                // yang menterminasi TLS, route() absolut bisa keluar sebagai
                // http:// walau halamannya https:// sehingga ajax-nya
                // diblokir browser ("mixed active content").
                url: @json(route('admin_approval_setting_employees', [], false)),
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return { q: params.term || '', page: params.page || 1 };
                },
                cache: true
            }
        });

        // Select2 ber-ajax tidak punya <option> yang sudah ter-render, jadi
        // .val(id) saja tidak cukup waktu Edit: option-nya dibuat dulu dari
        // label yang ikut dikirim di data-attribute tombol Edit.
        function setEmployeeSelection(selector, id, label) {
            const $select = $(selector);

            $select.find('option').not('[value=""]').remove();

            if (id) {
                $select.append(new Option(label || id, id, true, true));
            }

            $select.val(id || null).trigger('change');
        }

        $('#approval-setting-table').DataTable({
            // responsive: true DIHAPUS. Layout memuat ekstensi
            // dataTables.responsive 2.4.1 yang dibuat untuk DataTables 1.x;
            // dipakai pada DataTables 2.x dia melempar
            // "Cannot read properties of undefined (reading 'responsive')"
            // dari dalam .DataTable(), dan karena init ini ada di awal
            // $(document).ready, seluruh handler sesudahnya (Reset, Save,
            // Edit, Delete) jadi TIDAK pernah terpasang.
            //
            // Dulu error ini tidak muncul hanya karena halaman ini memuat
            // ulang DataTables 2.1.3 dari CDN paling akhir, sehingga ekstensi
            // responsive-nya tidak terpasang di objek yang dipakai - efek
            // samping yang sama yang bikin paginationnya tanpa gaya.
            //
            // Tabelnya memang tidak butuh ekstensi itu: pemendekan kolom dan
            // klik-untuk-melebar sudah ditangani CSS .cell-text / tr.expanded
            // di halaman ini.
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            searching: true,
            // Tabelnya table-layout: fixed, jadi tanpa lebar eksplisit semua
            // kolom dapat jatah yang sama - "No" dan "Action" jadi selebar
            // kolom teks, dan kolom teksnya kepotong lebih awal dari
            // seharusnya. Yang sempit dikunci, sisanya berbagi sisa ruang.
            columnDefs: [
                { orderable: false, targets: -1 },
                { className: "text-center", width: "56px", targets: 0 },
                { className: "text-center", width: "92px", targets: -1 },
                { width: "180px", targets: 1 },
                { width: "110px", targets: 2 },
            ]
        });

        $('#approval-setting-table tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('button').length) return;

            $(this).toggleClass('expanded');
        });

        $('#approval-setting-reset').on('click', async function (event) {
            event.preventDefault();
            $('.select2-single, .select2-multiple, .select2-employee').val(null).trigger('change');
            document.getElementById('approval-setting-form').reset();
            $('#approval-setting-submit')
                .removeClass('btn-warning');
            $('#approval-setting-submit')
                .addClass('btn-primary');
            $('#approval-setting-submit')
                .removeClass('text-black');
            $('#approval-setting-form-wrapper')
                .removeClass('border-5');
            $('#approval-setting-form-wrapper')
                .removeClass('border-warning');
            $('#approval-setting-form-wrapper')
                .addClass('border-0');
        });

        $('#approval-setting-submit').on('click', async function (event) {
            event.preventDefault();

            let btn = $(this);
            let id = btn.data('id');

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
                let response;

                if (id) {
                    formData.set('id', id);

                    response = await fetch('/admin/approval/setting/update', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                } else {
                    response = await fetch('/admin/approval/setting/create', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                }

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
                    $('.select2-single, .select2-multiple, .select2-employee').val(null).trigger('change');
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

        $('#approval-setting-table').on('click', '.approval-setting-edit', async function (event) {
            event.preventDefault();

            let btn = $(this);
            let id = btn.data('id');

            $('#approval-setting-submit').data('id', id);

            const form = document.getElementById('approval-setting-form');

            form.reset();

            $('.select2-single, .select2-multiple, .select2-employee').val(null).trigger('change');

            $('input[name="approval_name"]').val(btn.data('approval_name'));
            $('select[name="approval_type"]').val(btn.data('approval_type')).trigger('change');

            let groupCompanies = btn.data('group_companies');
            let contributionLevels = btn.data('contribution_level_codes');
            let workAreas = btn.data('work_areas');

            $('select[name="group_companies[]"]').val(groupCompanies ? groupCompanies.split(',') : []).trigger('change');
            $('select[name="contribution_level_codes[]"]').val(contributionLevels ? contributionLevels.split(',') : []).trigger('change');
            $('select[name="work_areas[]"]').val(workAreas ? workAreas.split(',') : []).trigger('change');

            setEmployeeSelection(
                'select[name="hcga_employee_id"]',
                btn.data('hcga_employee_id'),
                btn.data('hcga_employee_label')
            );
            setEmployeeSelection(
                'select[name="ktu_employee_id"]',
                btn.data('ktu_employee_id'),
                btn.data('ktu_employee_label')
            );

            $('html, body')
                .animate({ scrollTop: 0 }, 200)
                .promise()
                .done(function () {
                    $('#approval-setting-submit')
                        .removeClass('btn-primary');
                    $('#approval-setting-submit')
                        .addClass('btn-warning');
                    $('#approval-setting-submit')
                        .addClass('text-black');
                    $('#approval-setting-form-wrapper')
                        .removeClass('border-0');
                    $('#approval-setting-form-wrapper')
                        .addClass('border-5');
                    $('#approval-setting-form-wrapper')
                        .addClass('border-warning');
                });
        });

        $('#approval-setting-table').on('click', '.approval-setting-delete', async function () {
            let id = $(this).data('id');

            const confirmDelete = await Swal.fire({
                title: 'Delete',
                text: 'Apakah Anda yakin ingin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!'
            });

            if (!confirmDelete.isConfirmed) return;

            try {
                const response = await fetch('/admin/approval/setting/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                const result = await response.json();

                if (!response.ok) {
                    throw result;
                }

                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: result.message
                });

                location.reload();
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'Gagal menghapus data'
                });
            }
        });
    });
</script>
@endpush
