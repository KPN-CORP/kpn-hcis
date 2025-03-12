@extends('layouts_.vertical', ['page_title' => 'Business Trip'])

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="mb-3">
                    {{-- <a href="{{ url()->previous() }}" class="btn btn-outline-primary">
                    <i class="bi bi-caret-left-fill"></i> Kembali
                </a> --}}
                </div>
                <div class="card">
                    <div class="card-header d-flex bg-primary text-white justify-content-between">
                        <h4 class="mb-0">Add Data</h4>
                        <a href="{{ url()->previous() }}" type="button" class="btn-close btn-close-white"></a>
                    </div>
                    <div class="card-body">
                        <form action="/taksi/form/post" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control bg-light" id="nama" name="nama"
                                    style="cursor:not-allowed;" value="{{ $employee_data->fullname }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="divisi" class="form-label">Divisi</label>
                                <input type="text" class="form-control bg-light" id="divisi" name="divisi"
                                    style="cursor:not-allowed;" value="{{ $employee_data->unit }}" readonly>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="bb_perusahaan" class="form-label">Beban Biaya Perusahaan (PT Keperluan Dinas /
                                    Bukan PT Payroll)</label>
                                <select class="form-select" id="bb_perusahaan" name="bb_perusahaan">
                                    <option value="">--- Pilih PT ---</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->contribution_level_code }}">
                                            {{ $company->contribution_level . ' (' . $company->contribution_level_code . ')' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="sppd_bt" class="form-label">Beban Biaya Perusahaan (PT Keperluan Dinas /
                                    Bukan PT Payroll)</label>
                                <select class="form-select" id="sppd_bt" name="sppd_bt">
                                    <option value="">--- Pilih SPPD ---</option>
                                    @foreach ($sppd_bt as $bt)
                                        <option value="{{ $bt->id }}">
                                            {{ $bt->no_sppd }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nom_vt" class="form-label">Nominal</label>
                                <input type="number" class="form-control" id="nom_vt" name="nom_vt"
                                    placeholder="Nominal">
                            </div>
                            <div class="mb-3">
                                <label for="keeper_vt" class="form-label">Tujuan</label>
                                <input type="number" class="form-control" id="keeper_vt" name="keeper_vt"
                                    placeholder="...">
                            </div>
                            {{-- <div class="mb-3">
                                <label for="tujuan" class="form-label">Tujuan</label>
                                <input type="text" class="form-control" id="tujuan" name="tujuan"
                                    placeholder="Tujuan">
                            </div> --}}
                            {{-- <div class="mb-3">
                                <label for="keperluan" class="form-label">Keperluan (Agar diisi sesuai kunjungan
                                    dinas)</label>
                                <textarea class="form-control" id="keperluan" name="keperluan" rows="3"></textarea>
                            </div> --}}
                            {{-- <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ca" class="form-label">Cash Advanced</label>
                                    <select class="form-select" id="ca" name="ca">
                                        <option value="Tidak">Tidak</option>
                                        <option value="Ya">Ya</option>
                                    </select>
                                </div>
                            </div> --}}
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
