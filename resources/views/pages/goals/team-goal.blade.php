@extends('layouts_.vertical', ['page_title' => 'Team Goals'])

@section('css')
@endsection

@section('content')
    <!-- Begin Page Content -->
            <div class="container-fluid">
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
                  <div class="row bg-primary-subtle mb-3 p-1 rounded">
                    <div class="col-lg-auto text-center">
                      <div class="align-items-center">
                          <button class="btn btn-outline-primary rounded-pill btn-sm my-1 me-1 filter-btn" data-id="all">All Task</button>
                          <button class="btn btn-outline-primary rounded-pill btn-sm my-1 me-1 filter-btn" data-id="draft">Draft</button>
                          <button class="btn btn-outline-primary rounded-pill btn-sm my-1 me-1 filter-btn" data-id="waiting for revision">Waiting For Revision</button>
                          <button class="btn btn-outline-primary rounded-pill btn-sm my-1 me-1 filter-btn" data-id="waiting for approval">Waiting For Approval</button>
                          <button class="btn btn-outline-primary rounded-pill btn-sm my-1 me-1 filter-btn" data-id="approved">Approved</button>
                      </div>
                    </div>
                  </div>
                  <form id="formYearGoal" action="{{ route('team-goals') }}" method="GET">
                    <div class="row align-items-end">
                        @php
                            $filterYear = request('filterYear');
                        @endphp
                        <div class="col-sm col-md">
                            <div class="mb-3">
                                <label class="form-label" for="filterYear">Year</label>
                                <select name="filterYear" id="filterYear" onchange="yearGoal()" class="form-select" @style('width: 120px')>
                                    <option value="">select all</option>
                                    @foreach ($selectYear as $year)
                                        <option value="{{ $year->year }}" {{ $year->year == $filterYear ? 'selected' : '' }}>{{ $year->year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm col-md-auto">
                            <div class="mb-3">
                                <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="ri-search-line"></i></span>
                                    </div>
                                    <input type="text" name="customsearch" id="customsearch" class="form-control border-left-0" placeholder="search.." aria-label="search" aria-describedby="search">
                                    <div class="d-sm-none input-group-append">
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <div class="row px-2">
            <div class="col-lg-12 p-0">
                <div class="mt-3 p-2 bg-info bg-opacity-10 rounded shadow">
                    <div class="row">
                        <div class="col d-flex align-items-center">
                            <h5 class="m-0 w-100">
                                <a class="text-dark d-block" data-bs-toggle="collapse" href="#dataTasks" role="button" aria-expanded="false" aria-controls="dataTasks">
                                    <i class="ri-arrow-down-s-line fs-18"></i>Initiated <span class="text-muted">({{ count($tasks) }})</span>
                                </a>
                            </h5>
                        </div>
                        <div class="col-auto">
                            <form id="exportInitiatedForm" action="{{ route('team-goals.initiated') }}" method="POST">
                                @csrf
                                <input type="hidden" name="employee_id" id="employee_id" value="{{ Auth()->user()->employee_id }}">
                                @if (count($tasks))
                                    <button id="report-button" type="submit" class="btn btn-sm btn-outline-info rounded-pill float-end"><i class="ri-download-cloud-2-line me-1"></i><span>Download</span></button>
                                @endif
                            </form>
                        </div>
                    </div>
                    @foreach ($data as $row)
                    @endforeach
                    <div class="collapse show" id="dataTasks">
                        <div class="card mb-0 mt-2">
                            <div class="card-body" id="task-container-1">
                                <!-- task -->
                                @foreach ($tasks as $index => $task)
                                @php
                                    $subordinates = $task->subordinates;
                                    $firstSubordinate = $subordinates->isNotEmpty() ? $subordinates->first() : null;
                                    $formStatus = $firstSubordinate ? $firstSubordinate->goal->form_status : null;
                                    $goalId = $firstSubordinate ? $firstSubordinate->goal->id : null;
                                    $goalData = $firstSubordinate ? $firstSubordinate->goal['form_data'] : null;
                                    $createdAt = $firstSubordinate ? $firstSubordinate->formatted_created_at : null;
                                    $updatedAt = $firstSubordinate ? $firstSubordinate->formatted_updated_at : null;
                                    $updatedBy = $firstSubordinate ? $firstSubordinate->updatedBy : null;
                                    $status = $firstSubordinate ? $firstSubordinate->status : null;
                                    $approverId = $firstSubordinate ? $firstSubordinate->current_approval_id : null;
                                    $sendbackTo = $firstSubordinate ? $firstSubordinate->sendback_to : null;
                                    $employeeId = $firstSubordinate ? $firstSubordinate->employee_id : null;
                                    $sendbackTo = $firstSubordinate ? $firstSubordinate->sendback_to : null;
                                    $employeeName = $firstSubordinate ? $firstSubordinate->name : null;
                                    $approvalLayer = $firstSubordinate ? $firstSubordinate->approvalLayer : null;
                                @endphp
                                <div class="row mt-2 mb-2 task-card" data-status="{{ $formStatus == 'Draft' ? 'draft' : ($status == 'Pending' ? 'waiting for approval' : ($subordinates->isNotEmpty() ? ($status == 'Sendback' ? 'waiting for revision' : strtolower($status)) : 'no data')) }}">
                                    <div class="col">
                                        <div class="row mb-2">
                                            <div class="col-sm-6 mb-2 mb-sm-0">
                                                <div id="tooltip-container">
                                                    <img src="{{ asset('img/profiles/user.png') }}?v={{ config('app.version') }}" alt="image" class="avatar-xs rounded-circle me-1" data-bs-container="#tooltip-container" data-bs-toggle="tooltip" data-bs-placement="bottom"  data-bs-original-title="Initiated By {{ $task->employee->fullname.' ('.$task->employee->employee_id.')' }}">
                                                    {{ $task->employee->fullname }} <span class="text-muted">{{ $task->employee->employee_id }}</span>
                                                </div>
                                            </div> <!-- end col -->
                                        </div>
                                        <div class="row">
                                            <div class="col-lg col-sm-12 p-2">
                                                <h5>Initiated By</h5>
                                                <p class="mt-2 mb-0 text-muted">{{ $subordinates->isNotEmpty() ?$task->employee->fullname : '-' }}</p>
                                            </div>
                                            <div class="col-lg col-sm-12 p-2">
                                                <h5>Initiated Date</h5>
                                                <p class="mt-2 mb-0 text-muted">{{ $createdAt ? $createdAt : '-' }}</p>
                                            </div>
                                            <div class="col-lg col-sm-12 p-2">
                                                <h5>Updated By</h5>
                                                <p class="mt-2 mb-0 text-muted">{{ $updatedBy ? $updatedBy->name : '-' }}</p>
                                            </div>
                                            <div class="col-lg col-sm-12 p-2">
                                                <h5>Last Updated On</h5>
                                                <p class="mt-2 mb-0 text-muted">{{ $updatedAt ? $updatedAt : '-' }}</p>
                                            </div>
                                            <div class="col-lg col-sm-12 p-2">
                                                <h5>Status</h5>
                                                <a href="javascript:void(0)" data-bs-id="{{ $task->employee_id }}" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="{{ $formStatus == 'Draft' ? 'Draft' : ($approvalLayer ? 'Manager L'.$approvalLayer.' : '.$employeeName : $employeeName) }}" class="badge {{ $subordinates->isNotEmpty() ? ($formStatus == 'Draft' || $status == 'Sendback' ? 'bg-dark-subtle text-dark' : ($status === 'Approved' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning')) : 'bg-dark-subtle text-secondary'}} rounded-pill py-1 px-2">{{ $formStatus == 'Draft' ? 'Draft': ($status == 'Pending' ? 'Waiting For Approval' : ($subordinates->isNotEmpty() ? ($status == 'Sendback' ? 'Waiting For Revision' : $status) : 'No Data')) }}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        @if ($task->employee->employee_id == Auth::user()->employee_id || !$subordinates->isNotEmpty() || $formStatus == 'Draft')
                                            @if ($formStatus == 'submitted' || $formStatus == 'Approved')
                                            <a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $goalId }}"><i class="ri-file-text-line"></i></a>
                                            @endif
                                            @else
                                            @if ($approverId == Auth::user()->employee_id && $status === 'Pending' || $sendbackTo == Auth::user()->employee_id && $status === 'Sendback' || !$subordinates->isNotEmpty())
                                                <a href="{{ route('team-goals.approval', $goalId) }}" class="btn btn-outline-primary btn-sm rounded-pill font-weight-medium">Act</a>
                                            @else
                                                <a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalDetail{{ $goalId }}"><i class="ri-file-text-line"></i></a>
                                            @endif
                                        @endif
                                    </div>
                                    @if($index < count($tasks) - 1)
                                        <hr class="mb-1 mt-2">
                                    @endif
                                </div>
                                {{-- @if ($tasks) --}}
                                    @include('pages.goals.detail')
                                {{-- @endif --}}
                                @endforeach
                                <!-- end task -->
                                <div id="no-data-1" class="text-center" style="display: none;">
                                    <h5 class="text-muted">No Data</h5>
                                </div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card -->
                    </div> <!-- end .collapse-->
                </div>
                <div class="mt-3 p-2 bg-secondary bg-opacity-10 rounded shadow">
                    <div class="row">
                        <div class="col d-flex align-items-center">
                            <h5 class="m-0 w-100">
                                <a class="text-dark d-block" data-bs-toggle="collapse" href="#noDataTasks" role="button" aria-expanded="false" aria-controls="noDataTasks">
                                    <i class="ri-arrow-down-s-line fs-18"></i>Not Initiated <span class="text-muted">({{ count($notasks) }})</span>
                                </a>
                            </h5>
                        </div>
                        <div class="col-auto">
                            <form id="exportNotInitiatedForm" action="{{ route('team-goals.notInitiated') }}" method="POST">
                                @csrf
                                <input type="hidden" name="employee_id" id="employee_id" value="{{ Auth()->user()->employee_id }}">
                                @if (count($notasks))
                                    <button id="report-button" type="submit" class="btn btn-sm btn-outline-secondary rounded-pill float-end"><i class="ri-download-cloud-2-line me-1"></i><span>Download</span></button>
                                @endif
                            </form>
                        </div>
                    </div>
                
                    <div class="collapse show" id="noDataTasks">
                        <div class="card mt-2 mb-0 d-flex">
                            <div class="card-body align-items-center" id="task-container-2">
                                <!-- task -->
                                @foreach ($notasks as $index => $notask)
                                @php
                                    $subordinates = $row->request->subordinates;
                                    $firstSubordinate = $subordinates->isNotEmpty() ? $subordinates->first() : null;
                                    $formStatus = $firstSubordinate ? $firstSubordinate->goal->form_status : null;
                                    $goalId = $firstSubordinate ? $firstSubordinate->goal->id : null;
                                    $goalData = $firstSubordinate ? $firstSubordinate->goal['form_data'] : null;
                                    $createdAt = $firstSubordinate ? $firstSubordinate->created_at : null;
                                    $updatedAt = $firstSubordinate ? $firstSubordinate->updated_at : null;
                                    $updatedBy = $firstSubordinate ? $firstSubordinate->updatedBy : null;
                                    $status = $firstSubordinate ? $firstSubordinate->status : null;
                                    $approverId = $firstSubordinate ? $firstSubordinate->current_approval_id : null;
                                    $sendbackTo = $firstSubordinate ? $firstSubordinate->sendback_to : null;
                                    $employeeId = $firstSubordinate ? $firstSubordinate->employee_id : null;
                                    $sendbackTo = $firstSubordinate ? $firstSubordinate->sendback_to : null;
                                @endphp
                                <div class="row mt-2 mb-2 task-card" data-status="no data">
                                    <div class="col-sm-12 col-md p-2">
                                        <div id="tooltip-container">
                                            <img src="{{ asset('img/profiles/user.png') }}?v={{ config('app.version') }}" alt="image" class="avatar-xs rounded-circle me-1" data-bs-container="#tooltip-container" data-bs-toggle="tooltip" data-bs-placement="bottom"  data-bs-original-title="Initiated By {{ $notask->employee->fullname.' ('.$notask->employee->employee_id.')' }}">
                                            {{ $notask->employee->fullname }} <span class="text-muted">{{ $notask->employee->employee_id }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md p-2">
                                        <div class="h5 me-2 align-items-center">Date Of Joining :</div>
                                        <span class="align-items-center text-muted">{{ $notask->formatted_doj }}</span>
                                    </div>
                                    <div class="col-sm-12 col-md p-2">
                                        <div class="h5 me-2 align-items-center">Status :</div>
                                        <div><a href="javascript:void(0)" id="approval{{ $employeeId }}" data-toggle="tooltip" data-id="{{ $employeeId }}" class="badge bg-dark-subtle text-dark rounded-pill py-1 px-2">No Data</a></div>
                                    </div>
                                </div>
                                @if($index < count($notasks) - 1)
                                    <hr>
                                @endif
                                @endforeach
                                <!-- end task -->
                                <div id="no-data-2" class="text-center" style="display: none;">
                                    <h5 class="text-muted">No Data</h5>
                                </div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card -->
                    </div> <!-- end .collapse-->
                </div>
                
            </div>
        </div>
@endsection
@push('scripts')
<script src="{{ asset('js/goal-approval.js') }}?v={{ config('app.version') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const taskContainers = [document.getElementById('task-container-1'), document.getElementById('task-container-2')];
        const noDataMessages = [document.getElementById('no-data-1'), document.getElementById('no-data-2')];

        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                const filter = this.getAttribute('data-id');

                taskContainers.forEach((taskContainer, index) => {
                    const tasks = taskContainer.querySelectorAll('.task-card');
                    let visibleTaskCount = 0;

                    tasks.forEach(task => {
                        const taskStatus = task.getAttribute('data-status');

                        if (filter === 'all' || taskStatus === filter) {
                            task.style.display = 'flex';
                            visibleTaskCount++;
                        } else {
                            task.style.display = 'none';
                        }
                    });

                    if (visibleTaskCount === 0) {
                        noDataMessages[index].style.display = 'block';
                    } else {
                        noDataMessages[index].style.display = 'none';
                    }
                });
            });
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("customsearch");
        const taskCards = document.querySelectorAll(".task-card");
        const noDataMessages = [document.getElementById('no-data-1'), document.getElementById('no-data-2')];

        searchInput.addEventListener("input", function() {
            const searchValue = this.value.toLowerCase().trim();

            taskCards.forEach(function(card) {
                const cardContent = card.textContent.toLowerCase();
                if (cardContent.includes(searchValue)) {
                    card.style.display = "";
                    $('#report-button').css('display', 'block');
                } else {
                    $('#report-button').css('display', 'none');
                    card.style.display = "none";
                }
            });

            // Menampilkan pesan jika tidak ada hasil pencarian
            const noDataMessage = document.getElementById("no-data-2");
            const visibleCards = document.querySelectorAll(".task-card[style='display: block;']");
        });
    });
</script>
@endpush