@extends('layouts_.vertical', ['page_title' => 'Document'])

@section('css')
    <style>
        .preview-container {
            background: white;
            padding: 20px;
            min-height: 500px;
            border: 1px solid #ddd;
        }
        
        .form-panel {
            position: sticky;
            top: 20px;
        }
        
        .preview-panel {
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        /* Styling untuk konten DOCX */
        #previewContainer {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        
        #previewContainer p {
            margin-bottom: 1em;
        }
    </style>
@endsection

@section('content')
    <br>

    <div class="container-fluid py-3">
        <!-- Breadcrumb -->
        <div>
            <h4 class="mb-0 mt-2">{{ $parentLink }}</h4>
        </div>
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">{{ $parentLink }}</li>
                <li class="breadcrumb-item">📁 {{ $employeeId }}</li>
                <li class="breadcrumb-item active">📄 {{ $letter_name }}</li>
            </ol>
        </nav>

        <div class="row">
            <!-- Left Panel - Form -->
            <form action="{{ route('docGenerator.download') }}" method="POST" id="templateForm" class="d-flex">
                @csrf
                <input type="hidden" name="template_path" value="{{ str_replace('/storage/', '', $template_path) }}">
                {{-- {{dd($template_path);}} --}}
                <input type="hidden" name="letter_name" value="{{ $letter_name }}">
                <input type="hidden" name="action" id="formAction" value="save">

                <!-- Input Fields -->
                <div class="col-md-4 p-1">
                    <div class="card">
                        <div class="card-body" style="height: 600px; overflow-y: auto;">
                            {{-- <div class="form-group mb-3">
                                <label for="letter_name">Letter Name:</label>
                                <input type="text" class="form-control preview-update" name="letter_name" id="letter_name" value="{{ $letter_name }}">
                            </div> --}}
                            @if (!empty($placeholders))
                                @foreach ($placeholders as $placeholder)
                                    @php
                                        // Membuat ID yang aman untuk form
                                        $safeId = str_replace(' ', '_', strtolower($placeholder));
                                    @endphp
                                    <div class="form-group mb-3">
                                        <label for="{{ $safeId }}">{{ $placeholder }}:</label>
                                        <input type="text" 
                                            name="fields[{{ $placeholder }}]" 
                                            id="{{ $safeId }}" 
                                            required 
                                            class="form-control preview-update"
                                            data-placeholder="${{ $placeholder }}" 
                                            placeholder="Enter {{ $placeholder }}">
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Panel - DOCX Preview -->
                <div class="col-md-8 p-1">
                    <div class="card preview-panel">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Preview Document</h5>
                        </div>
                        <div class="card-body">
                            <div id="previewContainer" class="preview-container"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="position-fixed bottom-0 end-0 p-3">
            <a href="{{ route('docGenerator') }}" class="btn btn-secondary me-2">Close</a>
            <button class="btn btn-primary" type="submit" form="templateForm">Generate Document</button>
            <button class="btn btn-primary" type="button" id="previewButton">Preview Generate</button>
        </div>
    </div>    

    @include('hcis.document.navigation.modalGenerator')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
@endsection

@push('scripts')
    <script>
        let docxContent = '';

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function updatePreview() {
            if (!docxContent) return;

            let updatedContent = docxContent;
            const inputs = document.querySelectorAll('.preview-update');

            inputs.forEach(input => {
                const placeholder = input.dataset.placeholder;
                const value = input.value.trim() || placeholder;
                const regex = new RegExp(escapeRegExp(placeholder), 'g');
                updatedContent = updatedContent.replace(regex, value);
            });

            const previewContainer = document.getElementById('previewContainer');
            previewContainer.innerHTML = updatedContent;
        }

        function loadPreview() {
            const templateUrl = '{{ url($template_path) }}';
            console.log('Attempting to load template from:', templateUrl);

            fetch(templateUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.arrayBuffer();
                })
                .then(arrayBuffer => mammoth.convertToHtml({ arrayBuffer }))
                .then(result => {
                    docxContent = result.value;
                    updatePreview();
                    setupEventListeners();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('previewContainer').innerHTML = 
                        `<div class="alert alert-danger">Error loading preview: ${error.message}</div>`;
                });
        }
        console.log('Fuck',fetch('{{ asset('storage/app/public/'.$template_path) }}'));

        function setupEventListeners() {
            document.querySelectorAll('.preview-update').forEach(input => {
                input.addEventListener('input', updatePreview);
            });
        }

        document.addEventListener('DOMContentLoaded', loadPreview);
    </script>
    <script>
        document.getElementById('previewButton').addEventListener('click', function() {
            const form = document.getElementById('templateForm');
            const formData = new FormData(form);
            
            // Tampilkan loading state
            const previewContainer = document.getElementById('previewContainer');
            previewContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><div>Loading preview...</div></div>';

            // Debug: Log form data
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]); 
            }

            fetch('{{ route("docGenerator.preview") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(async response => {
                // Debug: Log response details
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    // Try to get error message from response
                    const errorText = await response.text();
                    console.error('Error response:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Preview response:', data); // Debug: Log response data
                if (data.success) {
                    return fetch('/' + data.preview_path);
                } else {
                    throw new Error(data.message || 'Failed to generate preview');
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load preview file');
                }
                return response.arrayBuffer();
            })
            .then(arrayBuffer => mammoth.convertToHtml({ arrayBuffer: arrayBuffer }))
            .then(result => {
                previewContainer.innerHTML = result.value;
            })
            .catch(error => {
                console.error('Error details:', error);
                previewContainer.innerHTML = `<div class="alert alert-danger">
                    Error generating preview: ${error.message}<br>
                    <small>Check console for more details</small>
                </div>`;
            });
        });
    </script>
@endpush