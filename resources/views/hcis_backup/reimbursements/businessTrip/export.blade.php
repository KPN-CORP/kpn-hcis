@extends('layouts_.vertical', ['page_title' => 'Business Trip'])
@section('content')
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 600px;
            padding: 20px;
            box-sizing: border-box;
        }

        .card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        h2 {
            font-size: 1.5rem;
            color: #333;
            margin-top: 0;
            margin-bottom: 15px;
        }

        .download-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }

        .download-item:last-child {
            border-bottom: none;
        }

        .download-button {
            display: inline-block;
            /* Change to inline-block to reduce width */
            padding: 4px 8px;
            /* Smaller padding */
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            /* Slightly smaller border-radius */
            cursor: pointer;
            font-size: 0.75rem;
            /* Smaller font size */
            text-align: center;
            margin-left: auto;
            /* Align to the right */
            transition: background-color 0.3s ease;
        }

        .download-button:hover {
            background-color: #0056b3;
        }

        .modal-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-secondary,
        .btn-primary {
            border: none;
            padding: 4px 8px;
            /* Smaller padding */
            border-radius: 4px;
            /* Slightly smaller border-radius */
            font-size: 0.75rem;
            /* Smaller font size */
            cursor: pointer;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            float: right;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Download Files for: {{ $sppd->nama ?? 'N/A' }}</h2>
            </div>
            <div class="card-body">
                <div class="download-item">
                    <label for="fileSppd">SPPD Document</label>
                    <button class="btn btn-primary download-button" data-type="sppd">Download</button>
                </div>

                @if ($sppd->ca == 'Ya' && isset($caTransactions[$sppd->no_sppd]))
                    <div class="download-item">
                        <label for="fileCa">CA Document</label>
                        <button class="btn btn-primary download-button" data-type="ca">Download</button>
                    </div>
                @endif

                @if ($sppd->hotel == 'Ya' && isset($hotel[$sppd->no_sppd]))
                    <div class="download-item">
                        <label for="fileHtl">Hotel Document</label>
                        <button class="btn btn-primary download-button" data-type="hotel">Download</button>
                    </div>
                @endif

                @if ($sppd->tiket == 'Ya' && isset($tickets[$sppd->no_sppd]))
                    <div class="download-item">
                        <label for="fileTkt">Tiket Document</label>
                        <button class="btn btn-primary download-button" data-type="tiket">Download</button>
                    </div>
                @endif

                @if ($sppd->taksi == 'Ya' && isset($taksi[$sppd->no_sppd]))
                    <div class="download-item">
                        <label for="fileTaksi">Taxi Document</label>
                        <button class="btn btn-primary download-button" data-type="taksi">Download</button>
                    </div>
                @endif

                <form action="{{ route('export', ['id' => $sppd->id]) }}" method="POST">
                    @csrf
                    <p>Are you sure you want to download all files?</p>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Download All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.download-button').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const id = '{{ $sppd->id }}';
                window.location.href = `/download-document/${id}/${type}`;
            });
        });
    </script>
@endsection
