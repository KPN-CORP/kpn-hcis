@extends('layouts_.vertical', ['page_title' => 'Travel'])

@section('css')
    <style>
        .menu-card {
            text-decoration: none;
            /* Menghapus underline dari link */
        }

        .card {
            border: none;
            /* Menghapus border default card */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Menambahkan shadow */
            transition: transform 0.3s;
            /* Menambahkan animasi transisi */
        }

        .card:hover {
            transform: translateY(-10px);
            /* Efek saat di-hover */
        }

        .card-body {
            padding: 5px;
            /* Menambahkan padding pada card body */
        }

        .card-img {
            max-width: 100px;
            /* Mengatur ukuran maksimal gambar */
            margin: 0 auto;
            /* Memusatkan gambar */
            display: block;
            /* Memastikan gambar menjadi blok */
        }

        h5 {
            margin-top: 5px;
            /* Menambahkan jarak atas pada heading */
            margin-bottom: 0;
            /* Menghapus jarak bawah pada heading */
            color: #333;
            /* Mengatur warna teks */
        }
    </style>
@endsection

@section('content')
    <!-- Begin Page Content -->
    {{-- {{ "Hallo ".$userId." berasal dari system ".session('system') }} --}}
    <br>

    <div class="container-fluid">
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 row-cols-xxl-8 text-center">
            <div class="col">
                <a href="{{ route('approval.cashadvanced') }}">
                    <div class="card" style="height: 200px">
                        <div class="card-body">
                            <img src="{{ asset('images/menu/approval.png') }}" alt="logo">
                            <h5 class="my-3">Approval</h5>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </a>
            </div> <!-- end col-->

            <div class="col">
                <a href="{{ route('businessTrip') }}">
                    <div class="card" style="height: 200px">
                        <div class="card-body">
                            <img src="{{ asset('images/menu/bt.png') }}" alt="logo"
                                style="width: 100px; height: 100px;">
                            <h5 class="my-3">Business Trip</h5>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </a>
            </div> <!-- end col-->

            <div class="col">
                <a href="{{ route('ticket') }}">
                    <div class="card" style="height: 200px">
                        <div class="card-body">
                            <img src="{{ asset('images/menu/tkt.png') }}" alt="logo"
                                style="width: 100px; height: 100px;">
                            <h5 class="my-3">Ticket</h5>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </a>
            </div>

            <div class="col">
                <a href="{{ route('hotel') }}">
                    <div class="card" style="height: 200px">
                        <div class="card-body">
                            <img src="{{ asset('images/menu/ht.png') }}" alt="logo"
                                style="width: 100px; height: 100px;">
                            <h5 class="my-3">Hotel</h5>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </a>
            </div>

            @if(!empty(trim(Auth::user()->employee->homebase ?? '')) && (preg_match('/^[4-9]/', $jobLevel)))
                <div class="col-md-3">
                    <a href="{{ route('home-trip') }}">
                        <div class="card" style="height: 200px">
                            <div class="card-body">
                                <img src="{{ asset('images/menu/home-trip.png') }}" alt="logo"
                                    style="width: 100px; height: 100px; border-radius: 100px;">
                                <h5 class="my-3">Home Trip</h5>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </a>
                </div><!-- end col-->
            @endif
        </div> <!-- end row -->

        {{-- HR ADMIN --}}
        @if (auth()->check() &&
                (auth()->user()->can('report_hcis_bt') ||
                    auth()->user()->can('report_hcis_ht') ||
                    auth()->user()->can('report_hcis_tkt') ||
                    auth()->user()->can('report_hcis_htl')))
            <div style="display: flex; align-items: center; margin: 20px 0;">
                <hr style="flex-grow: 1; border: none; border-top: 1px solid #ddd; margin: 0;">
                <span style="padding: 0 20px; font-weight: bold;">Admin</span>
                <hr style="flex-grow: 1; border: none; border-top: 1px solid #ddd; margin: 0;">
            </div>
        @endif
        {{-- END HR ADMIN --}}

        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 row-cols-xxl-8 text-center">
            @if (auth()->check())
                @can('report_hcis_bt')
                    <div class="col-md-3">
                        <a href="{{ route('businessTrip.admin') }}">
                            <div class="card" style="height: 200px">
                                <div class="card-body">
                                    <img src="/images/menu/report.png" alt="logo">
                                    <h5 class="my-3">Business Trip (Admin)</h5>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </a>
                    </div>
                @endcan
            @endif
            @if (auth()->check())
                @can('report_hcis_ht')
                    <div class="col-md-3">
                        <a href="{{ route('home-trip.admin') }}">
                            <div class="card" style="height: 200px">
                                <div class="card-body">
                                    <img src="{{ asset('images/menu/report.png') }}" alt="logo"
                                        style="width: 100px; height: 100px; border-radius: 100px;">
                                    <h5 class="my-3">Home Trip (Admin)</h5>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </a>
                    </div> <!-- end col-->
                @endcan
            @endif
            @if (auth()->check())
                @can('report_hcis_tkt')
                    <div class="col-md-3">
                        <a href="{{ route('ticket.admin') }}">
                            <div class="card" style="height: 200px">
                                <div class="card-body">
                                    <img src="{{ asset('images/menu/report.png') }}" alt="logo">
                                    <h5 class="my-3">Ticket (Admin)</h5>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </a>
                    </div> <!-- end col-->
                @endcan
            @endif
            @if (auth()->check())
                @can('report_hcis_htl')
                    <div class="col-md-3">
                        <a href="{{ route('hotel.admin') }}">
                            <div class="card" style="height: 200px">
                                <div class="card-body">
                                    <img src="{{ asset('images/menu/report.png') }}" alt="logo">
                                    <h5 class="my-3">Hotel (Admin)</h5>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </a>
                    </div> <!-- end col-->
                @endcan
            @endif
        </div>
    @endsection

    @push('scripts')
    @endpush
