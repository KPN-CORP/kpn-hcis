@extends('layouts_.vertical', ['page_title' => 'Designations'])

@section('css')
    <style>
        /* Penataan kotak processing (overlay, posisi titik) sudah ada di
           _preloader.scss, jadi TIDAK diulang di sini. Yang belum tertangani
           cuma satu hal: _preloader.scss berasumsi markup language.processing
           kita yang jadi "> div:last-child". Ternyata tidak - DataTables
           membangun kotaknya dengan .html(language.processing) lalu
           .append("<div>...4 div...</div>"), jadi anak terakhirnya adalah
           spinner biru bawaannya sendiri dan ikut tampil di samping titik
           .bouncing-loader kita.

           Specificity-nya lewat div.dt-container karena blok style ini
           dirender di <head> SEBELUM dataTables.bootstrap5.min.css. */
        div.dt-container div.dt-processing > div:last-child {
            display: none;
        }
    </style>
@endsection

@section('content')
    @include('pages.designations.modal')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">{{ $parentLink }}</li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="row align-items-end g-2 mb-3">
            <div class="col-12 col-md-3">
                <label class="form-label" for="filter-business-unit">Business Unit</label>
                {{-- Nilainya dari parent_company_id yang ada di tabel. Cuma
                     tujuh, jadi dirender langsung dan dicari di browser. --}}
                <select class="form-select select2-filter" id="filter-business-unit"
                    data-placeholder="All Business Unit">
                    <option value=""></option>
                    @foreach ($businessUnits as $businessUnit)
                        <option value="{{ $businessUnit }}">{{ $businessUnit }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label" for="filter-designation-name">Designation Name</label>
                {{-- Nilai uniknya 3.435, jadi dicari lewat designations.names
                     dan bukan dirender semua di sini. --}}
                <select class="form-select" id="filter-designation-name"
                    data-placeholder="All Designation Name">
                    <option value=""></option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label" for="customsearch">Search</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                    </div>
                    <input type="text" name="customsearch" id="customsearch"
                        class="form-control  border-dark-subtle border-left-0" placeholder="search.."
                        aria-label="search" aria-describedby="search">
                </div>
            </div>

            <div class="col-12 col-md-2">
                <button type="button" id="filter-reset" class="btn btn-outline-secondary w-100">Reset</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover dt-responsive nowrap" id="defaultTable" width="100%"
                                cellspacing="0">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Parent Company ID</th>
                                        <th>Designation Name</th>
                                        <th>Job Code</th>
                                        <th>Dept Head Flag</th>
                                        <th>Director Flag</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Baris tabel diisi DataTables server-side dari
                                         designations.data. Versi lama di bawah merender
                                         SELURUH designation sekaligus. Disimpan utuh
                                         sebagai pembanding.

                                    @foreach ($designations as $designation)
                                        <tr>
                                            <td style="text-align: center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $designation->parent_company_id }}</td>
                                            <td>{{ $designation->designation_name }}</td>
                                            <td>{{ $designation->job_code }}</td>
                                            <td class="text-center">
                                                @if ($designation->dept_head_flag === 'T')
                                                    <form
                                                        action="{{ route('designations.update.dept', ['id' => $designation->id]) }}"
                                                        method="POST" style="display:inline;"
                                                        id="dept-head-update-form-{{ $designation->id }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden"
                                                            id="dept-designation-id-{{ $designation->id }}"
                                                            value="{{ $designation->job_code }}">
                                                        <button
                                                            class="btn btn-sm rounded-pill btn-outline-success update-button"
                                                            title="Change Boolean" data-id="{{ $designation->id }}"
                                                            type="button">
                                                            True
                                                        </button>
                                                    </form>
                                                @else
                                                    <form
                                                        action="{{ route('designations.update.dept', ['id' => $designation->id]) }}"
                                                        method="POST" style="display:inline;"
                                                        id="dept-head-update-form-{{ $designation->id }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden"
                                                            id="dept-designation-id-{{ $designation->id }}"
                                                            value="{{ $designation->job_code }}">
                                                        <button
                                                            class="btn btn-sm rounded-pill btn-outline-danger update-button"
                                                            title="Change Boolean" data-id="{{ $designation->id }}"
                                                            type="button">
                                                            False
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($designation->director_flag === 'T')
                                                    <form
                                                        action="{{ route('designations.update.director', ['id' => $designation->id]) }}"
                                                        method="POST" style="display:inline;"
                                                        id="director-update-form-{{ $designation->id }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden"
                                                            id="director-designation-id-{{ $designation->id }}"
                                                            value="{{ $designation->job_code }}">
                                                        <button
                                                            class="btn btn-sm rounded-pill btn-outline-success update-button"
                                                            title="Change Boolean" data-id="{{ $designation->id }}"
                                                            type="button">
                                                            True
                                                        </button>
                                                    </form>
                                                @else
                                                    <form
                                                        action="{{ route('designations.update.director', ['id' => $designation->id]) }}"
                                                        method="POST" style="display:inline;"
                                                        id="director-update-form-{{ $designation->id }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden"
                                                            id="director-designation-id-{{ $designation->id }}"
                                                            value="{{ $designation->job_code }}">
                                                        <button
                                                            class="btn btn-sm rounded-pill btn-outline-danger update-button"
                                                            title="Change Boolean" data-id="{{ $designation->id }}"
                                                            type="button">
                                                            False
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    --}}
                                </tbody>
                                @if (session('message'))
                                    <script>
                                        alert('{{ session('message') }}');
                                    </script>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
@endsection
{{-- Init DataTables sengaja ditaruh di stack "scripts", bukan di dalam
     bagian content.

     vertical.blade.php merender @stack('scripts') paling akhir, sesudah
     @include('script') yang memuat vendor/datatables/dataTables.min.js dan
     dataTables.bootstrap5.min.js. Integrasi Bootstrap 5 itulah yang
     menggambar paging/length/info; kalau tabelnya di-init lebih awal (di
     dalam content, tepat sesudah <script> DataTables dari CDN), renderer
     yang terpasang masih yang polos dan paginationnya keluar tanpa gaya. --}}
@push('scripts')
    <script>
        // Business Unit: pilihannya sudah ter-render, select2 cuma dipakai
        // supaya bisa diketik untuk mencari.
        $(".select2-filter").select2({
            theme: "bootstrap-5",
            width: "100%",
            allowClear: true,
            placeholder: function() {
                return $(this).data("placeholder");
            },
        });

        // Designation Name: nilainya dicari di server, 30 sekali ambil, dan
        // ikut menyesuaikan Business Unit yang sedang dipilih.
        $("#filter-designation-name").select2({
            theme: "bootstrap-5",
            width: "100%",
            allowClear: true,
            placeholder: function() {
                return $(this).data("placeholder");
            },
            minimumInputLength: 0,
            ajax: {
                url: @json(route('designations.names', [], false)),
                dataType: "json",
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term || "",
                        page: params.page || 1,
                        business_unit: $("#filter-business-unit").val() || "",
                    };
                },
                cache: true,
            },
        });

        var designationTable = $("#defaultTable").DataTable({
            // Kotak pencarian bawaan dimatikan: halaman ini sudah punya
            // #customsearch sendiri di atas tabel.
            dom: "lrtip",
            processing: true,
            serverSide: true,
            searchDelay: 400,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            ajax: {
                // URL relatif (absolute: false): di belakang reverse proxy
                // yang menterminasi TLS, route() absolut bisa keluar sebagai
                // http:// walau halamannya https:// sehingga ajax-nya
                // diblokir browser ("mixed active content").
                url: @json(route('designations.data', [], false)),
                data: function(d) {
                    // Nilai dua dropdown filter ikut dikirim tiap tarikan.
                    d.business_unit = $("#filter-business-unit").val() || "";
                    d.designation_name = $("#filter-designation-name").val() || "";
                },
            },
            columnDefs: [
                // Nomor urut dihitung dari offset halaman, bukan dari kolom
                // di database, jadi tidak bisa diurutkan.
                {
                    orderable: false,
                    targets: [0]
                },
                {
                    className: "text-center align-middle",
                    targets: [0, 4, 5]
                },
                {
                    className: "align-middle",
                    targets: [1, 2, 3]
                },
            ],
            language: {
                // Titik-titik yang sama seperti #preloader. Penataan kotak
                // processing-nya ada di _preloader.scss; spinner bawaan
                // DataTables disembunyikan lewat CSS di blok style halaman.
                processing: '<div class="dt-loader"><div class="bouncing-loader"><div></div><div></div><div></div></div></div>',
            },
        });

        // Kotak "search.." di halaman ini sebelumnya tidak tersambung ke apa
        // pun; sekarang menyaring lewat server.
        $("#customsearch").on("input", function() {
            designationTable.search(this.value).draw();
        });

        $("#filter-business-unit").on("change", function() {
            // Daftar Designation Name menyesuaikan Business Unit, jadi
            // pilihan lamanya belum tentu masih ada di dalamnya.
            $("#filter-designation-name").val(null).trigger("change.select2");
            designationTable.ajax.reload();
        });

        $("#filter-designation-name").on("change", function() {
            designationTable.ajax.reload();
        });

        $("#filter-reset").on("click", function() {
            $("#filter-business-unit").val(null).trigger("change.select2");
            $("#filter-designation-name").val(null).trigger("change.select2");
            $("#customsearch").val("");
            designationTable.search("").draw();
        });

        // #preloader (loader.blade.php) memakai .bouncing-loader yang sama
        // dengan kotak processing tabel, dan app.js baru menyembunyikannya di
        // window.load + delay 350ms + fade "slow", jadi dua animasi yang
        // identik tampil bersamaan. Dimatikan begitu tabelnya dibuat.
        //
        // Ini TIDAK bisa lewat .on("processing.dt"): DataTables menyalakan
        // processing secara sinkron di dalam .DataTable() di atas, jadi event
        // pertamanya sudah lewat sebelum listener-nya sempat terpasang.
        if (typeof hideLoader === "function") {
            hideLoader();
        } else {
            $("#preloader").hide();
        }
    </script>
@endpush
