@extends('layouts_.vertical', ['page_title' => 'Schedule'])

@section('css')
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
                    <h4 class="page-title">{{ $link }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="mb-2 text-end">
                    <a href="{{ route('schedules.form') }}" class="btn btn-primary rounded-pill shadow">Create Schedule</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-auto">
              <div class="mb-3">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-dark-subtle"><i class="ri-search-line"></i></span>
                  </div>
                  <input type="text" name="customsearch" id="customsearch" class="form-control  border-dark-subtle border-left-0" placeholder="search.." aria-label="search" aria-describedby="search">
                </div>
              </div>
            </div>
        </div>
        <!-- Content Row -->
        <div class="row">
          <div class="col-md-12">
            <div class="card shadow mb-4">
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover dt-responsive nowrap" id="scheduleTable" width="100%" cellspacing="0">
                          <thead class="thead-light">
                              <tr class="text-center">
                                  <th>#</th>
                                  <th>Name</th>
                                  <th>Type</th>
                                  <th>From</th>
                                  <th>To</th>
                                  <th>Reminder</th>
                                  <th>Days</th>
                                  <th>Created By</th>
                                  <th>Actions</th>
                              </tr>
                          </thead>
                          <tbody>

                            @foreach($schedules as $schedule)
                              <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $schedule->schedule_name }}</td>
                                    <td>{{ $schedule->employee_type }}</td>
                                    <td>{{ $schedule->start_date }}</td>
                                    <td>{{ $schedule->end_date }}</td>
                                    <td>@if($schedule->checkbox_reminder == '1') Yes @else No @endif</td>
                                    <td>@if($schedule->checkbox_reminder == 1)
                                            @if($schedule->repeat_days <> '')
                                                {{ $schedule->repeat_days }}
                                            @else
                                                {{ $schedule->before_end_date . ' Days Before End Date' }}
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ isset($schedule->createdBy->name) ? $schedule->createdBy->name : '-' }}</td>
                                    <!--<td><span class="badge badge-success badge-pill w-100">Active</span></td>-->
                                    <td class="text-center">
                                        @if($schedule->created_by == $userId)
                                            <a href="{{ route('edit-schedule', $schedule->id) }}" class="btn btn-sm rounded-pill btn-primary" title="Edit" ><i class="ri-edit-box-line"></i></a>
                                            
                                            <a class="btn btn-sm rounded-pill btn-danger" title="Delete" onclick="handleDelete(this)" data-id="{{ $schedule->id }}"><i class="ri-delete-bin-line"></i></a>
                                        @endif
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
    </div>
@endsection

@push('scripts')
<script>
    // Periksa apakah ada pesan sukses
    var successMessage = "{{ session('success') }}";

    // Jika ada pesan sukses, tampilkan sebagai alert
    if (successMessage) {
        alert(successMessage);
    }
</script>
<script>
        function handleDelete(element) {
            var scheduleId = element.getAttribute('data-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This schedule will deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/schedule/' + scheduleId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('An error occurred while deleting the data.');
                        }
                        Swal.fire(
                            'Deleted!',
                            'Your data has been deleted.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the data.',
                            'error'
                        );
                    });
                }
            });
        }
    
</script>
@endpush
