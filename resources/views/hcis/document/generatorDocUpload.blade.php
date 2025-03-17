@extends('layouts_.vertical', ['page_title' => 'Document'])

@section('css')
@endsection

@section('content')
    <!-- Begin Page Content -->
    {{-- {{ "Hallo ".$userId." berasal dari system ".session('system') }} --}}
    <br>

    <div class="container mt-4">
        <!-- Header with back button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                {{-- <a href="#" class="text-decoration-none text-secondary">
                    <i class="bi bi-arrow-left"></i> Back To Letters
                </a> --}}
                <h4 class="mb-0 mt-2">{{ $parentLink }}</h4>
            </div>
            <div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                        <i class="bi bi-plus"></i> Add
                    </button>
                </div>
                <a href="" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-gear"></i> Manage Letters
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link active text-danger" href="#">TEMPLATES</a>
            </li>
            {{-- <li class="nav-item">
                <a class="nav-link text-secondary" href="#">CLAUSES</a>
            </li> --}}
        </ul>

        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Search by template name" name="customsearch" id="customsearch" aria-label="search" aria-describedby="search" >
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm dt-responsive nowrap mt-2" id="scheduleTable" width="100%"
                            cellspacing="0">
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th>No</th>
                                    <th class="sticky-col-header" style="background-color: white">Document</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $document)
                                    <tr>
                                        <td class="text-center" >{{ $loop->index + 1 }}</td>
                                        <td style="background-color: white;" class="sticky-col">
                                            <div class="list-group-item">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        <h6 class="mb-0">{{ $document->letter_name }}</h6>
                                                        <small class="text-muted">Last Updated On: {{ $document->updated_at }}</small>
                                                    </div>
                                                    <div class="col-auto">
                                                        <a href="{{ route('docGenerator.edit', $document->id) }}" class="btn btn-outline-warning" title="Edit" ><i class="ri-edit-box-line"></i></a>
                                                        <form class="deleteForm" action="{{ route('docGenerator.delete', $document->id) }}" method="POST" style="display: inline;">  
                                                            @csrf  
                                                            @method('DELETE')  
                                                            <button type="button" class="btn btn-outline-danger deleteButton">  
                                                                <i class="bi bi-trash"></i>  
                                                            </button>  
                                                        </form> 
                                                    </div>
                                                </div>
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
    </div>

    @include('hcis.document.navigation.modalGenerator')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection

@push('scripts')
@endpush
