@extends('layouts_.vertical', ['page_title' => 'Medical (Admin)'])

@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css', 'node_modules/daterangepicker/daterangepicker.css', 'node_modules/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.css', 'node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css', 'node_modules/bootstrap-timepicker/css/bootstrap-timepicker.min.css', 'node_modules/flatpickr/dist/flatpickr.min.css'])
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
            border-bottom: 2px solid #AB2F2B !important;
            padding-right: 6px;
            box-shadow: inset 2px 0 0 #AB2F2B;
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
            border-right: 2px solid #AB2F2B !important;
            padding-right: 10px;
            box-shadow: inset 2px 0 0 #AB2F2B;
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

        /* Penataan kotak processing (overlay, posisi titik, menetralkan gaya
           titik DataTables) sudah ada di _preloader.scss, jadi TIDAK diulang
           di sini. Yang belum tertangani cuma satu hal:

           _preloader.scss berasumsi markup language.processing kita yang jadi
           "> div:last-child". Ternyata tidak: DataTables membangun kotaknya
           dengan .html(language.processing) lalu .append("<div>...4 div...</div>"),
           jadi anak terakhirnya adalah spinner biru bawaan DataTables, dan
           .dt-loader kita cuma anak pertama. Spinner itu tetap tampil di
           samping titik-titik .bouncing-loader kita - dua animasi sekaligus.

           Specificity-nya lewat div.dt-container karena blok style ini
           dirender di <head> SEBELUM dataTables.bootstrap5.min.css. */
        div.dt-container div.dt-processing > div:last-child {
            display: none;
        }
    </style>
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
                            <li class="breadcrumb-item">{{ $parentLink }}</li>
                            <li class="breadcrumb-item active">{{ $link }}</li>
                        </ol>
                    </div>
                    <a href="{{ route('reimbursements') }}" class="mb-2 mt-2 me-4 page-title"><i class="ri-arrow-left-circle-line"></i></a>
                    <a href="{{ route('ticket.admin', ['tkt_type' => 'Cuti']) }}" class="mb-2 mt-2 btn btn-primary"> <i class="bi bi-file-earmark-post"></i> Report  </a>  
                </div>
            </div>
        </div>
        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form action="{{ route('home-trip.admin') }}" method="GET">
                            <div class="container-fluid p-2">
                                <div class="row align-items-end g-1">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label">Unit Location:</label>
                                        <select class="form-select select2" aria-label="Status" id="stat"
                                            name="stat">
                                            <option value="" {{ request()->get('stat') == '-' ? 'selected' : '' }}>All
                                                Location</option>
                                            @foreach ($locations as $location)
                                                <option value="{{ $location->work_area }}"
                                                    {{ $location->work_area == request()->get('stat') ? 'selected' : '' }}>
                                                    {{ $location->area . ' (' . $location->company_name . ')' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-5">
                                        <label class="form-label">Employee Name</label>
                                        <input type="text" name="customsearch" id="customsearch" value="{{ request()->get('customsearch') }}" class="form-control"
                                            placeholder="Employee Name">
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <button class="btn btn-primary w-100" type="submit">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="tableFilter">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="card-title">{{ $link }}</h3>
                        </div>
                        <div class="table-responsive">
                            {{-- id-nya sengaja BUKAN "scheduleTable":
                                 public/js/script.js meng-init #scheduleTable
                                 sebagai DataTable client-side (pageLength 50)
                                 untuk belasan halaman lain, dan itulah yang
                                 selama ini memegang tabel ini. Sekarang
                                 tabelnya server-side, jadi dipisahkan supaya
                                 init global itu tidak ikut memasang kontrol
                                 paging/info-nya sendiri. --}}
                            <table class="table table-sm dt-responsive nowrap mt-2" id="htReportTable" width="100%"
                                cellspacing="0">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th class="text-center">No</th>
                                        <th class="text-center">NIK</th>
                                        <th class="text-center">Employee ID</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Join Date</th>
                                        <th class="text-center">Family Count</th>
                                        <th class="text-center">Period</th>
                                        <th class="text-center">Ticket Count</th>
                                        <th class="text-center">Detail Plafond</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Baris tabel diisi DataTables server-side dari
                                         home-trip.admin.data. Versi lama di bawah
                                         merender SELURUH karyawan hasil filter
                                         sekaligus, dan tiap barisnya memicu tiga query
                                         lagi di controller. Disimpan utuh sebagai
                                         pembanding.

                                    @if (request()->get('stat') == '-')
                                    @else
                                        @foreach ($ht_employee as $ht_employees)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td class="text-center">{{ $ht_employees->ktp }}</td>
                                                <td class="text-center">{{ $ht_employees->employee_id }}</td>
                                                <td class="text-center">{{ $ht_employees->fullname }}</td>
                                                <td class="text-center">{{ $ht_employees->date_of_joining }}</td>
                                                <td class="text-center">{{ $ht_employees->family_count }}</td>
                                                <td class="text-center">{{ $ht_employees->latest_period }}</td>
                                                <td class="text-center">{{ $ht_employees->ticket_count }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('home-trip.detail', encrypt($ht_employees->employee_id)) }}"
                                                        class="btn btn-outline-warning" title="Edit">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('hcis.reimbursements.medical.navigation.modalMedical')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
@endsection
@section('script')
    {{-- @vite(['resources/js/pages/demo.form-advanced.js']) --}}

    <!-- Include jQuery -->
    {{-- <script src="{{ asset('vendor/bootstrap/js/jquery-3.6.0.min.js') }}"></script> --}}

    <!-- Include Bootstrap Date Range Picker -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    {{-- <script src="{{ asset('/js/cashAdvanced/adminPage.js') }}"></script> --}}
    {{-- @include('js.hcis.cashAdvanced.adminPage') --}}
@endsection
@push('scripts')
    {{-- Init DataTables ada di stack ini, bukan di dalam bagian content.

         vertical.blade.php merender @stack('scripts') paling akhir, sesudah
         @include('script') yang memuat vendor/datatables/dataTables.min.js dan
         dataTables.bootstrap5.min.js. Integrasi Bootstrap 5 itulah yang
         menggambar paging/length/info; kalau tabelnya di-init lebih awal (di
         dalam content, tepat sesudah <script> DataTables dari CDN), renderer
         yang terpasang masih yang polos dan paginationnya keluar tanpa gaya. --}}
    <script>
        var htReportTable = $("#htReportTable").DataTable({
            // Kotak pencarian bawaan dimatikan: penyaringan halaman ini lewat
            // form Unit Location / Employee Name di atas tabel.
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
                url: @json(route('home-trip.admin.data', [], false)),
                data: function(d) {
                    // Ikut filter yang sedang aktif di URL halaman. Tanpa
                    // filter, endpoint-nya mengembalikan nol baris, sama
                    // seperti versi lama.
                    d.stat = @json(request()->get('stat'));
                    d.customsearch = @json(request()->get('customsearch'));
                },
            },
            columnDefs: [
                // No, Family Count, Period, Ticket Count dan Detail Plafond
                // dihitung di luar SQL, jadi tidak bisa diurutkan.
                {
                    orderable: false,
                    targets: [0, 5, 6, 7, 8]
                },
                {
                    className: "text-center align-middle",
                    targets: "_all"
                },
            ],
            language: {
                // Titik-titik yang sama seperti #preloader. Penataan kotak
                // processing-nya ada di _preloader.scss; spinner bawaan
                // DataTables disembunyikan lewat CSS di blok style halaman.
                processing: '<div class="dt-loader"><div class="bouncing-loader"><div></div><div></div><div></div></div></div>',
            },
        });

        // #preloader (loader.blade.php) memakai .bouncing-loader yang sama
        // dengan kotak processing tabel, dan app.js baru menyembunyikannya di
        // window.load + delay 350ms + fade "slow". Ajax halaman pertama jalan
        // jauh sebelum itu, jadi dua animasi yang identik tampil bersamaan.
        // Begitu tabelnya dibuat, preloader-nya dimatikan: halamannya sendiri
        // sudah selesai diparse, yang ditunggu tinggal datanya, dan itu sudah
        // diwakili overlay tabel.
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
    <script>
        // Periksa apakah ada pesan sukses
        var successMessage = "{{ session('success') }}";

        // Jika ada pesan sukses, tampilkan sebagai alert
        if (successMessage) {
            Swal.fire({
                title: "Success!",
                text: successMessage,
                icon: "success",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: "Ok",
            });
        }

        function redirectToExportExcel() {
            const route = "{{ route('exportmed.excel') }}";

            // Ambil nilai dari input
            const stat = document.getElementById("stat").value;
            const customSearch = document.getElementById("customsearch").value;

            // Buat elemen form
            const form = document.createElement("form");
            form.method = "GET";
            form.action = route;

            // Tambahkan input tersembunyi untuk setiap parameter
            const statInput = document.createElement("input");
            statInput.type = "hidden";
            statInput.name = "stat";
            statInput.value = stat;

            const customSearchInput = document.createElement("input");
            customSearchInput.type = "hidden";
            customSearchInput.name = "customsearch";
            customSearchInput.value = customSearch;

            // Tambahkan input ke form
            form.appendChild(statInput);
            form.appendChild(customSearchInput);

            // Tambahkan form ke body dan kirim
            document.body.appendChild(form);
            form.submit();
        }

        // Event listener untuk menangkap date range yang dipilih
        $("#singledaterange").on("apply.daterangepicker", function(ev, picker) {
            var startDate = picker.startDate.format("YYYY-MM-DD");
            var endDate = picker.endDate.format("YYYY-MM-DD");

            // Panggil fungsi untuk mendapatkan data yang difilter
            filterTableByDateRange(startDate, endDate);
        });

        // Fungsi untuk mengirimkan tanggal yang dipilih ke server dan memperbarui tabel
        function filterTableByDateRange(startDate, endDate) {
            $.ajax({
                url: '{{ route('cashadvanced.admin') }}', // Route yang sudah Anda buat
                type: "GET",
                data: {
                    start_date: startDate,
                    end_date: endDate,
                },
                success: function(response) {
                    // Perbarui tabel dengan data yang difilter
                    // $('#scheduleTable tbody').html(response);
                    // $('#tableFilter').html(response);
                },
                error: function(xhr) {
                    // console.error(xhr);
                },
            });
        }
        //script modal
    </script>
@endpush
