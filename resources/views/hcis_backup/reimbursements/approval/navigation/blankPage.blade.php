<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <meta http-equiv="X-UA-Compatible" content="ie=edge">  
    <title>Approval Page</title>  

    <!-- Bootstrap CSS -->  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">  
    <!-- SweetAlert CSS -->  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">  

    <style>  
        .btn-primary {  
            background-color: #9a2a27; /* Warna latar belakang */  
            border-color: #9a2a27; /* Warna border */  
        }  
        .btn-primary:hover {  
            background-color: #7e2320; /* Warna latar belakang saat hover */  
            border-color: #7e2320; /* Warna border saat hover */  
        }  
        .btn-primary:focus, .btn-primary.focus {  
            box-shadow: none; /* Menonaktifkan efek box shadow */  
            outline: none; /* Menonaktifkan outline */  
        } 
    </style>  
</head>  
<body>  
    <div class="container">  
        {{-- <h1 class="mt-5">Hello! This is a blank page</h1>   --}}
        {{-- @php
            $detailCA = json_decode($transactions->detail_ca, true) ?? [];
        @endphp --}}

        @if ($transactions)  
            <button id="rejectButton" type="button" class="btn mb-2 btn-primary btn-pill px-4 me-2" style="display: none" data-bs-toggle="modal" data-bs-target="#modalReject"  
                data-no-id="{{ $transactions->id }}"  
                data-no-ca="{{ $transactions->no_ca }}"  
                data-name="{{ $transactions->employee->fullname }}"  
                data-start-date="{{ $transactions->start_date }}"  
                data-end-date="{{ $transactions->end_date }}"  
                data-destination="{{ $transactions->destination == 'Others' ? $transactions->others_location : $transactions->destination }}"  
                data-purposes="{{ $transactions->ca_needs }}"
                data-pt="{{ $transactions->companies->contribution_level }}"
                data-area="{{ $transactions->contribution_level_code }}"
                data-catype="{{ $transactions->type_ca }}"
                {{-- data-caDetail="{{ $transactions->detail_ca }}"
                data-caDeclare="{{ $transactions->declare_ca }}" --}}
                {{-- data-caDetail="{{ json_encode($detailCA['detail_perdiem']) }}"
                data-transport="{{ json_encode($detailCA['detail_transport']) }}"
                data-accommodation="{{ json_encode($detailCA['detail_penginapan']) }}"
                data-other="{{ json_encode($detailCA['detail_lainnya']) }}" --}}
                data-total-days="{{ $transactions->total_days }}">  
                Reject  
            </button>  

            <div class="modal fade" id="modalReject" tabindex="-1" aria-labelledby="modalRejectLabel" aria-hidden="true">  
                <div class="modal-dialog modal-lg">  
                    <div class="modal-content">  
                        <div class="modal-header" style="background-color:#9a2a27">  
                            <h1 class="modal-title text-center text-white fs-5" id="modalRejectLabel">Reject - <label id="reject_no_ca"></label></h1>  
                            <button type="button" class="btn-close btn-close-white" id="closeModalButton" aria-label="Close"></button>  
                        </div>  
                        <form method="POST" action="{{   
                            $transactions->approval_extend == 'Pending'   
                                ? route('approval.email.ext', ['id' => $transactions->id, 'employeeId' => $transactions->status_id])   
                                : ($transactions->approval_status == 'Approved'   
                                    ? route('approval.email.dec', ['id' => $transactions->id, 'employeeId' => $transactions->status_id])   
                                    : route('approval.email', ['id' => $transactions->id, 'employeeId' => $transactions->status_id]))   
                        }}">  
                            @csrf  
                            <div class="modal-body">
                                <div class="row mb-3">  
                                    <div class="col-md-2"><b>No Dokumen</b></div>  
                                    <div class="col-md-1 text-center"><b>:</b></div>  
                                    <div class="col-md-7" id="reject_no_ca_2"></div>  
                                </div>  
                                <div class="row mb-3">  
                                    <div class="col-md-2"><b>Name</b></div>  
                                    <div class="col-md-1 text-center"><b>:</b></div>  
                                    <div class="col-md-7" id="reject_name"></div>  
                                </div>  
                                <div class="row mb-3">  
                                    <div class="col-md-2"><b>Start date</b></div>  
                                    <div class="col-md-1 text-center"><b>:</b></div>  
                                    <div class="col-md-7" id="reject_start"></div>  
                                </div>  
                                <div class="row mb-3">  
                                    <div class="col-md-2"><b>End Date</b></div>  
                                    <div class="col-md-1 text-center"><b>:</b></div>  
                                    <div class="col-md-7" id="reject_end"></div>  
                                </div>  
                                <div class="row mb-3">  
                                    <div class="col-md-2"><b>Destination</b></div>  
                                    <div class="col-md-1 text-center"><b>:</b></div>  
                                    <div class="col-md-7" id="reject_destination"></div>  
                                </div>  
                                <div class="row mb-3">  
                                    <div class="col-md-2"><b>Purposes</b></div>  
                                    <div class="col-md-1 text-center"><b>:</b></div>  
                                    <div class="col-md-7" id="reject_purposes"></div>  
                                </div>  
                                <div class="row mb-3">  
                                    <div class="col-md-2"><b>PT</b></div>  
                                    <div class="col-md-1 text-center"><b>:</b></div>  
                                    <div class="col-md-4"><label id="reject_pt"></label>(<label id="reject_are"></label>)</div>  
                                </div>  
                                <div class="row mb-3">  
                                    <div class="col-md-2"><b>CA Type</b></div>  
                                    <div class="col-md-1 text-center"><b>:</b></div>  
                                    <div class="col-md-7" id="reject_type"></div>  
                                </div>                            
                                <hr class="border border-danger border-2 opacity-50">
                                <div class="row">  
                                    <div class="col-md-12 mb-2">  
                                        <label class="form-label" for="reason"><b>Reasons</b></label>  
                                        <textarea name="reject_info" id="reject_info" class="form-control" required></textarea>  
                                    </div>  
                                    <input type="hidden" name="reject_no_id" id="reject_no_id">  
                                </div>  
                            </div>  
                            <div class="modal-footer">  
                                <button type="button" class="btn btn-secondary" id="cancelButton">Close</button>  
                                <button type="submit" name="action_ca_reject" value="Reject" class="btn btn-primary btn-pill px-4 me-2">Reject</button>  
                            </div>  
                        </form>  
                    </div>  
                </div>  
            </div>  
        @endif  

        @if (session('success'))  
            <script>  
                document.addEventListener('DOMContentLoaded', function () {  
                    Swal.fire({  
                        title: "Success!",  
                        text: "{{ session('success') }}",  
                        icon: "success",  
                        confirmButtonColor: "#9a2a27",  
                        confirmButtonText: 'Ok'  
                    }).then((result) => {  
                        if (result.isConfirmed) {  
                            window.close();  
                        }  
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
                    }).then((result) => {  
                        if (result.isConfirmed) {  
                            window.close();  
                        }  
                    });  
                });  
            </script>  
        @endif 

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>  
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>  
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>  

        <script>  
            document.addEventListener("DOMContentLoaded", function() {  
                const editButtons = document.querySelectorAll('[data-bs-toggle="modal"]');  

                // Jika tombol "Reject" diklik  
                editButtons.forEach(button => {  
                    button.addEventListener('click', function() {  
                        const caNumber = this.getAttribute('data-no-ca');  
                        const idNumber = this.getAttribute('data-no-id');
                        const caName = this.getAttribute('data-name');
                        const caStart = this.getAttribute('data-start-date');
                        const caEnd = this.getAttribute('data-end-date');
                        const caDestination = this.getAttribute('data-destination');  
                        const caPurpose = this.getAttribute('data-purposes');  
                        const caPT = this.getAttribute('data-pt');  
                        const caArea = this.getAttribute('data-area');  
                        const caType = this.getAttribute('data-catype');  

                        // Mengisi nilai di modal  
                        document.getElementById('reject_no_ca').textContent = caNumber;  
                        document.getElementById('reject_no_ca_2').textContent = caNumber;  
                        document.getElementById('reject_name').textContent = caName;  
                        document.getElementById('reject_start').textContent = caStart;  
                        document.getElementById('reject_end').textContent = caEnd;  
                        document.getElementById('reject_destination').textContent = caDestination;  
                        document.getElementById('reject_purposes').textContent = caPurpose;  
                        document.getElementById('reject_pt').textContent = caPT;  
                        document.getElementById('reject_are').textContent = caArea;  
                        document.getElementById('reject_type').textContent = caType;  
                        document.getElementById('reject_no_id').value = idNumber;  

                        // if (caType === 'dns') {
                        //     document.getElementById('ca_bt').style.display = 'block';

                        //     // Ambil data dari atribut tombol
                        //     const perdiemData = JSON.parse(this.getAttribute('data-caDetail'));
                        //     const transportData = JSON.parse(this.getAttribute('data-transport') || "[]");
                        //     const accommodationData = JSON.parse(this.getAttribute('data-accommodation') || "[]");
                        //     const otherData = JSON.parse(this.getAttribute('data-other') || "[]");
                        //     const totalCA = parseInt(this.getAttribute('data-total-ca'), 10);
                        //     const perdiemTableBody = document.getElementById('perdiemTableBody');
                        //     perdiemTableBody.innerHTML = ''; // Bersihkan tabel

                        //     let totalPerdiem = 0;
                        //     let totalDays = 0;

                        //     // Memproses data perdiem
                        //     perdiemData.forEach((perdiem, index) => {
                        //         totalPerdiem += parseInt(perdiem.nominal, 10);
                        //         totalDays += parseInt(perdiem.total_days, 10);

                        //         const row = document.createElement('tr');
                        //         row.classList.add('text-center');
                        //         row.innerHTML = `
                        //             <td>${index + 1}</td>
                        //             <td>${formatDate(perdiem.start_date)}</td>
                        //             <td>${formatDate(perdiem.end_date)}</td>
                        //             <td>${perdiem.location === 'Others' ? perdiem.other_location : perdiem.location}</td>
                        //             <td>${perdiem.company_code}</td>
                        //             <td>${perdiem.total_days} Days</td>
                        //             <td style="text-align: center">${formatCurrency(perdiem.nominal)}</td>
                        //         `;
                        //         perdiemTableBody.appendChild(row);
                        //     });

                        //     // Menampilkan total di footer
                        //     document.getElementById('totalDays').textContent = `${totalDays} Days`;
                        //     document.getElementById('totalAmount').textContent = `${formatCurrency(totalPerdiem)}`;

                        //     // Isi data di tabel Detail Cash Advanced
                        //     const cashAdvanceTableBody = document.getElementById('cashAdvanceTableBody');
                        //     cashAdvanceTableBody.innerHTML = '';

                        //     cashAdvanceTableBody.innerHTML += `
                        //         <tr>
                        //             <td class="text-center">Perdiem</td>
                        //             <td>${totalDays > 0 ? totalDays + ' Days' : '-'}</td>
                        //             <td style="text-align: right"> ${formatCurrency(totalPerdiem)}</td>
                        //         </tr>
                        //         <tr>
                        //             <td class="text-center">Transport</td>
                        //             <td>-</td>
                        //             <td style="text-align: right"> ${formatCurrency(sumNominal(transportData))}</td>
                        //         </tr>
                        //         <tr>
                        //             <td class="text-center">Accommodation</td>
                        //             <td>${sumDays(accommodationData)} Nights</td>
                        //             <td style="text-align: right"> ${formatCurrency(sumNominal(accommodationData))}</td>
                        //         </tr>
                        //         <tr>
                        //             <td class="text-center">Others</td>
                        //             <td>-</td>
                        //             <td style="text-align: right"> ${formatCurrency(sumNominal(otherData))}</td>
                        //         </tr>
                        //     `;

                        //     // Menampilkan total amount keseluruhan
                        //     const grandTotal = totalPerdiem + sumNominal(transportData) + sumNominal(accommodationData) + sumNominal(otherData);
                        //     document.getElementById('totalCAAmount').textContent = ` ${formatCurrency(grandTotal)}`;
                        // } else {
                        //     document.getElementById('ca_bt').style.display = 'none';
                        // }
                    });  
                });

                function formatDate(dateStr) {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-GB', {
                        day: '2-digit', month: 'short', year: '2-digit'
                    });
                }

                function formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
                }

                function sumNominal(dataArray) {
                    return dataArray.reduce((sum, item) => sum + parseInt(item.nominal, 10), 0);
                }

                function sumDays(dataArray) {
                    return dataArray.reduce((sum, item) => sum + parseInt(item.total_days, 10), 0);
                }

                const urlParams = new URLSearchParams(window.location.search);  
                if (urlParams.get('autoOpen') === 'reject') {  
                    const rejectButton = document.getElementById('rejectButton');  
                    if (rejectButton) {  
                        rejectButton.click();  
                    }  
                }  

                const cancelButton = document.getElementById('cancelButton');  
                const closeModalButton = document.getElementById('closeModalButton');  
                const modal = document.getElementById('modalReject');  
                cancelButton.addEventListener('click', closeModalAndWindow);  
                closeModalButton.addEventListener('click', closeModalAndWindow);  

                // Add event listener for keydown (to close modal with Esc)  
                document.addEventListener('keydown', function(event) {  
                    if (event.key === 'Escape') {  
                        closeModalAndWindow();  
                    }  
                });  

                function closeModalAndWindow() {  
                    const modalInstance = bootstrap.Modal.getInstance(modal);  
                    if (modalInstance) {  
                        modalInstance.hide();  
                    }  
                    window.close();  
                }  
            });


        </script>  
    </div>  
</body>  
</html>