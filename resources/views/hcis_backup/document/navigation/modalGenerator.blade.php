<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('docGenerator.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelHealtCoverage">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12 mb-2">
                        <label class="form-label" for="letter_name">Letter Name</label>
                        <input type="text" name="letter_name" class="form-control" id="letter_name" placeholder="Letter Name" required>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label for="template">Template (docx):</label>
                        <input type="file" class="form-control" name="template" id="template" accept=".docx" accept=".docx" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>  
    // Attach the event listener to the delete buttons  
    document.querySelectorAll('.deleteButton').forEach(function(button) {  
        button.addEventListener('click', function() {  
            const form = this.closest('form'); // Get the closest form  
            Swal.fire({  
                title: 'Are you sure you want to delete this document?',  
                icon: 'warning',  
                showCancelButton: true,  
                confirmButtonColor: '#d33',  
                cancelButtonColor: '#3085d6',  
                confirmButtonText: 'Yes, delete it!'  
            }).then((result) => {  
                if (result.isConfirmed) {  
                    form.submit(); // Submit the form if confirmed  
                }  
            });  
        });  
    });  
</script> 

@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: "Success!",
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'Ok'
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: "Error!",
                text: "{{ session('error') }}",
                icon: "error",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'Ok'
            });
        });
    </script>
@endif