@extends('layouts_.vertical', ['page_title' => 'Designations'])

@section('css')
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

        {{-- Search --}}
        <div class="row">
            <div class="col-md-auto">
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                        </div>
                        <input type="text" name="customsearch" id="customsearch"
                            class="form-control  border-dark-subtle border-left-0" placeholder="search.."
                            aria-label="search" aria-describedby="search">
                    </div>
                </div>
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
@push('scripts')
@endpush
