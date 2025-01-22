{{-- Excel Health Coverage Limit --}}
<div class="modal fade" id="importExcelHealtCoverage" tabindex="-1" aria-labelledby="importExcelHealtCoverageLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('import.medical') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelHealtCoverage">Import Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12 mb-2">
                        <label class="form-label" for="file">Import File</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="form-label" for="imp_attachment">File Attachment (PDF)</label>
                        <input type="file" class="form-control" name="imp_attachment" id="imp_attachment" accept=".pdf">
                        <div id="pdfPreviewWrapper" style="margin-top: 10px;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('download-template') }}" class="btn btn-primary">Download Template</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Plafond Admin --}}
@if (auth()->user()->hasRole('superadmin'))
    <div class="modal fade" id="editPlafond" tabindex="-1" aria-labelledby="editPlafondLabel" aria-hidden="true">
        <div class="modal-dialog" style="wid">
            <form method="POST" action="{{ route('medical.edit.plafon', ['period' => ':period', 'employee' => ':employee']) }}" enctype="multipart/form-data" class="confirm-submit">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPlafond">Edit Plafon - <label id="period_plafond"></label></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="dynamic-inputs"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>    
@endif

{{-- Success --}}
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: "Success!",
                text: "{{ session('success') }}",
                icon: "success",
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'OK',
            });
        });
    </script>
@endif

{{-- Error --}}
@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: `
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                `,
                confirmButtonColor: "#9a2a27",
                confirmButtonText: 'OK',
            });
        });
    </script>
@endif

{{-- Preview Import Attachment --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const attachmentInput = document.getElementById("imp_attachment");
        const pdfPreviewWrapper = document.getElementById("pdfPreviewWrapper");

        attachmentInput.addEventListener("change", function () {
            const file = this.files[0];
            pdfPreviewWrapper.innerHTML = ""; // Clear previous preview

            if (file) {
                const fileSizeInMB = file.size / (1024 * 1024);
                const fileExtension = file.name.split(".").pop().toLowerCase();

                if (fileExtension !== "pdf") {
                    Swal.fire({
                        title: "Unsupported Format!",  
                        text: "Please upload a file in PDF format!",  
                        icon: "warning",
                        confirmButtonColor: "#9a2a27",
                        confirmButtonText: 'OK',
                    });
                    this.value = ""; // Reset input
                    return;
                }

                if (fileSizeInMB > 3) {
                    Swal.fire({
                        title: "File Size Too Large!",  
                        text: "File size must not exceed 3MB!",  
                        icon: "warning",
                        confirmButtonColor: "#9a2a27",
                        confirmButtonText: 'OK',
                    });
                    this.value = ""; // Reset input
                    return;
                }

                // Create preview
                const link = document.createElement("a");
                link.href = URL.createObjectURL(file);
                link.target = "_blank";
                link.rel = "noopener noreferrer";

                const icon = document.createElement("img");
                icon.src = "{{ asset('images/pdf_icon.png') }}";
                icon.style.maxWidth = "48px";
                icon.style.marginTop = "10px";
                link.appendChild(icon);

                const text = document.createElement("p");
                text.textContent = "Click to view PDF";
                text.style.marginTop = "5px";

                pdfPreviewWrapper.appendChild(link);
                pdfPreviewWrapper.appendChild(text);
            }
        });
    });
</script>

{{-- Confirmation Submit --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.confirm-submit').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault(); // Cegah submit form langsung

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to Edit this Plafond?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit form jika dikonfirmasi
                    }
                });
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent form from submitting immediately

                const transactionId = button.getAttribute('data-id');
                const form = document.getElementById(`deleteForm_${transactionId}`);
                const noSppd = document.getElementById(`no_sppd_${transactionId}`).value;

                Swal.fire({
                    title: `Do you want to delete this request?\n (${noSppd})`,
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#AB2F2B", // Confirm button color
                    cancelButtonColor: "#CCCCCC", // Cancel button color
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Only submit the form if the user confirms
                    }
                });
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editPlafond = document.getElementById('editPlafond');

        editPlafond.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;

            // Ambil period dan employee
            var medicalPeriod = button.getAttribute('data-period');
            var employeeId = button.getAttribute('data-employee');

            // Tampilkan period di elemen dengan ID "period_plafond"
            document.getElementById("period_plafond").textContent = medicalPeriod;

            // Update action form
            var form = editPlafond.querySelector('form');
            var action = form.getAttribute('action');
            action = action.replace(':period', medicalPeriod);
            action = action.replace(':employee', employeeId);
            form.setAttribute('action', action);

            // Ambil semua data-* atribut
            var dataAttributes = button.dataset;

            // Kontainer untuk input dinamis
            var dynamicInputsContainer = document.getElementById('dynamic-inputs');
            dynamicInputsContainer.innerHTML = ''; // Reset kontainer

            // Daftar atribut yang dikecualikan
            var excludedKeys = ['period', 'bsToggle', 'bsTarget', 'employee'];

            // Loop untuk setiap atribut data-* kecuali yang dikecualikan
            for (var key in dataAttributes) {
                if (!excludedKeys.includes(key)) {
                    var labelName = key.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                    var value = dataAttributes[key];
                    var isNegative = parseFloat(value) < 0;

                    // Buat elemen input
                    var inputGroup = `
                        <div class="col-md-12 mb-2">
                            <label class="form-label" for="${labelName}">${labelName}</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input 
                                    class="form-control form-control-sm format-number ${isNegative ? 'text-danger bg-light' : ''}" 
                                    name="${labelName}" 
                                    id="${labelName}" 
                                    type="text" 
                                    value="${formatNumber(value)}"
                                    ${isNegative ? 'readonly' : ''}
                                >
                            </div>
                        </div>
                    `;
                    dynamicInputsContainer.insertAdjacentHTML('beforeend', inputGroup);
                }
            }

            // Tambahkan event listener untuk format angka
            document.querySelectorAll('.format-number:not([readonly])').forEach(function (input) {
                input.addEventListener('input', function (e) {
                    var cursorPosition = input.selectionStart; // Simpan posisi kursor
                    input.value = formatNumber(input.value.replace(/\./g, '')); // Format angka
                    input.setSelectionRange(cursorPosition, cursorPosition); // Kembalikan posisi kursor
                });
            });
        });

        // Fungsi untuk memformat angka dengan titik setiap 3 digit
        function formatNumber(value) {
            if (!value) return '';
            var isNegative = parseFloat(value) < 0;
            var absValue = Math.abs(parseFloat(value) || 0).toString();
            return (isNegative ? '-' : '') + absValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    });
</script>
