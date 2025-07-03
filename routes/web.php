<?php

use App\Http\Controllers\Admin\OnBehalfController as AdminOnBehalfController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SendbackController as AdminSendbackController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ApprovalReimburseController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BusinessTripController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BussinessTripController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\ExportExcelController;
use App\Http\Controllers\MedicalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LayerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SendbackController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\TaksiController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyGoalController;
use App\Http\Controllers\TeamGoalController;
use App\Http\Controllers\ReimburseController;
use App\Http\Controllers\HomeTripController;
use App\Http\Controllers\BTApprovalController;
use App\Http\Controllers\ApiEmployeeController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\DocumentGeneratorController;
use App\Models\Designation;
use Faker\Provider\Medical;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('reimbursements');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'role:superadmin'])->name('dashboard');

Route::get('dbauth', [SsoController::class, 'dbauth']);
Route::get('sourcermb/dbauth', [SsoController::class, 'dbauthReimburse']);
Route::get('sourcetrv/dbauth', [SsoController::class, 'dbauthTravel']);

Route::get('fetch-employees', [EmployeeController::class, 'fetchAndStoreEmployees']);
Route::get('fetch-employees2', [EmployeeController::class, 'fetchAndStoreEmployees2']);
Route::get('inactive-employees', [EmployeeController::class, 'EmployeeInactive']);
Route::get('updmenu-employees', [EmployeeController::class, 'updateEmployeeAccessMenu']);
Route::get('daily-schedules', [ScheduleController::class, 'reminderDailySchedules']);
Route::get('update-designtaion', [DesignationController::class, 'UpdateDesignation']);
// Route::get('generate-weeklyoff-shift', [AttendanceController::class, 'GenerateWeeklyShiftOff']);
// Route::get('backup-daily-attendance', [AttendanceController::class, 'BackupDailyAttendance']);
// Route::get('add-backdated-attendance', [AttendanceController::class, 'AddBackdatedAttendance']);
Route::get('update-bt-to-db', [AttendanceController::class, 'UpdateBTtoDB']);
Route::get('update-bt-to-db-nextstep', [AttendanceController::class, 'UpdateBTtoDBnextstep']);

Route::get('/test-email', function () {
    $messages = '<p>This is a test message with <strong>bold</strong> text.</p>';
    $name = 'John Doe';

    return view('email.reminderschedule', compact('messages', 'name'));
});

Route::get('/send-test-email', function () {
    $testEmailAddress = 'eriton.dewa@kpn-corp.com'; // Ganti dengan alamat email tujuan
    $messageContent = 'This is a test email sent from Laravel.';

    try {
        Mail::to($testEmailAddress)->send(new TestEmail($messageContent));
        return 'Test email has been sent successfully!';
    } catch (\Exception $e) {
        return 'Failed to send email. Error: ' . $e->getMessage();
    }
});
Route::get('/apiemployees/{id}', [ApiEmployeeController::class, 'show']);

//LINK APPROVAL BT
Route::get('approve/{id}/{manager_id}/{status}', [BTApprovalController::class, 'approveFromLink'])->name('approve.business.trip');

Route::get('businessTrip/revision/{id}/{manager_id}/{status}', [BTApprovalController::class, 'revisionLink'])->name('revision.link');
Route::get('businessTrip/rejection/{id}/{manager_id}/{status}', [BTApprovalController::class, 'rejectLink'])->name('reject.link');
Route::post('revision/{id}/{manager_id}/{status}', [BTApprovalController::class, 'revisionFromLink'])->name('revision.business.trip');
Route::post('reject/{id}/{manager_id}/{status}', [BTApprovalController::class, 'rejectFromLink'])->name('reject.business.trip');

//LINK APPROVAL Declarations BT
Route::get('approve/declaration/{id}/{manager_id}/{status}', [BTApprovalController::class, 'approveFromLinkDeklarasi'])->name('approve.business.trip.declare');

Route::get('businessTrip/declaration/revision/{id}/{manager_id}/{status}', [BTApprovalController::class, 'revisionDeclarationLink'])->name('revision.link.declaration');
Route::get('businessTrip/declaration/rejection/{id}/{manager_id}/{status}', [BTApprovalController::class, 'rejectDeclarationLink'])->name('reject.link.declaration');
Route::post('reject/declaration/{id}/{manager_id}/{status}', [BTApprovalController::class, 'rejectDeclarationFromLink'])->name('reject.business.trip.declaration');
Route::post('revision/declaration/{id}/{manager_id}/{status}', [BTApprovalController::class, 'revisionDeclarationFromLink'])->name('revision.business.trip.declaration');

// LINK APPROVAL EXTEND BT
Route::get('approve/extend/{id}/{manager_id}/{status}', [BTApprovalController::class, 'approveFromLinkExtend'])->name('approve.business.trip.extend');
Route::get('reject/extend/{id}/{manager_id}/{status}', [BTApprovalController::class, 'rejectExtendFromLink'])->name('reject.business.trip.extend');

//LINK APPROVAL Hotel
Route::get('approve/hotel/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'approveHotelFromLink'])->name('approve.hotel');

Route::get('hotel/rejection/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'rejectHotelLink'])->name('reject.hotel.link');
Route::post('reject/hotel/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'rejectHotelFromLink'])->name('reject.hotel');
Route::get('hotel/revision/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'revisionHotelLink'])->name('revision.hotel.link');
Route::post('revision/hotel/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'revisionHotelFromLink'])->name('revision.hotel');

//LINK APPROVAL TICKETS
Route::get('approve/ticket/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'approveTicketFromLink'])->name('approve.ticket');

Route::get('ticket/rejection/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'rejectTicketLink'])->name('reject.ticket.link');
Route::get('ticket/revision/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'revisionTicketLink'])->name('revision.ticket.link');
Route::post('reject/ticket/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'rejectTicketFromLink'])->name('reject.ticket');
Route::post('revision/ticket/{id}/{manager_id}/{status}', [ApprovalReimburseController::class, 'revisionTicketFromLink'])->name('revision.ticket');

// LINK APPROVAL CA REQUEST
Route::get('/approval/cashadvanced/approve/email/{id}/{employeeId}', [ApprovalReimburseController::class, 'cashadvancedActionApprovalEmail'])
    ->name('approval.email.aproved');

Route::post('/approval/cashadvanced/reject/email/{id}/{employeeId}', [ApprovalReimburseController::class, 'cashadvancedActionApprovalEmail'])
    ->name('approval.email');

// LINK APPROVAL CA DEKLARASI
Route::get('/approval/cashadvancedDec/approved/email/{id}/{employeeId}', [ApprovalReimburseController::class, 'cashadvancedActionDeklarasiEmail'])
    ->name('approval.email.approveddec');

Route::post('/approval/cashadvancedDec/reject/email/{id}/{employeeId}', [ApprovalReimburseController::class, 'cashadvancedActionDeklarasiEmail'])
    ->name('approval.email.dec');

// LINK APPROVAL CA EXTEND
Route::get('/approval/cashadvancedExt/approved/email/{id}/{employeeId}', [ApprovalReimburseController::class, 'cashadvancedActionExtendEmail'])
    ->name('approval.email.approvedext');

Route::post('/approval/cashadvancedExt/reject/email/{id}/{employeeId}', [ApprovalReimburseController::class, 'cashadvancedActionExtendEmail'])
    ->name('approval.email.ext');

// DIRECT AFTER APPROVE OR REJECT HALAMAN KOSONG
Route::get('/page/notification', [ReminderController::class, 'emailReminder'])->name('email.reminderDec');

Route::get('/page/{key}', [ApprovalReimburseController::class, 'blankApproval'])->name('blank.page');

Route::get('/page', [ApprovalReimburseController::class, 'blankApproval'])->name('blank.pageUn');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::get('reset-password-email', [PasswordResetLinkController::class, 'selfResetView']);

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
});


Route::middleware('auth')->group(function () {

    Route::get('reset-self', [PasswordResetLinkController::class, 'selfReset'])
        ->name('password.reset.self');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'task'])->name('tasks');

    // My Menu
    Route::get('/reimbursements', [ReimburseController::class, 'reimbursements'])->name('reimbursements');
    Route::get('/travel', [ReimburseController::class, 'travel'])->name('travel');

    Route::get('/generator', [DocumentGeneratorController::class, 'GeneratorDoc'])->name('docGenerator');
    Route::get('/generator/edit/{id}', [DocumentGeneratorController::class, 'GeneratorEdit'])->name('docGenerator.edit');
    Route::post('/generator/upload', [DocumentGeneratorController::class, 'GeneratorUpload'])->name('docGenerator.upload');
    Route::post('/generator/preview', [DocumentGeneratorController::class, 'GeneratorPreview'])->name('docGenerator.preview');
    Route::post('/generator/download', [DocumentGeneratorController::class, 'GeneratorDownload'])->name('docGenerator.download');
    Route::delete('/doc-generator/{id}', [DocumentGeneratorController::class, 'GeneratorDelete'])->name('docGenerator.delete');

    // My Cash Advanced
    Route::get('/cashadvanced', [ReimburseController::class, 'cashadvanced'])->name('cashadvanced');
    Route::get('/cashadvanced/form', [ReimburseController::class, 'cashadvancedCreate'])->name('cashadvanced.form');
    Route::post('/cashadvanced/submit', [ReimburseController::class, 'cashadvancedSubmit'])->name('cashadvanced.submit');
    Route::get('/cashadvanced/edit/{id}', [ReimburseController::class, 'cashadvancedEdit'])->name('cashadvanced.edit');
    Route::post('/cashadvanced/update/{id}', [ReimburseController::class, 'cashadvancedUpdate'])->name('cashadvanced.update');
    Route::post('/cashadvanced/delete/{id}', [ReimburseController::class, 'cashadvancedDelete'])->name('cashadvanced.delete');
    Route::get('/cashadvanced/download/{id}', [ReimburseController::class, 'cashadvancedDownload'])->name('cashadvanced.download');

    // My Cash Advanced

    Route::get('/exportca/excel', [ReimburseController::class, 'exportExcel'])->name('exportca.excel');
    Route::get('/filter-ca-transactions', [ReimburseController::class, 'filterCaTransactions'])->name('filter.ca.transactions');

    Route::middleware(['permission:reportca_hcis'])->group(function () {
        Route::get('/cashadvanced/admin', [ReimburseController::class, 'cashadvancedAdmin'])->name('cashadvanced.admin');
        Route::post('/cashadvanced/adupdate/{id}', [ReimburseController::class, 'cashadvancedAdminUpdate'])->name('cashadvanced.adupdate');

        Route::post('/cashadvanced/approval/submit/{id}', [ApprovalReimburseController::class, 'cashadvancedActionApprovalAdmin'])->name('approvalAdmin.cashadvancedApprovedAdmin');
        Route::post('/cashadvanced/approvalDec/submit/{id}', [ApprovalReimburseController::class, 'cashadvancedActionDeklarasiAdmin'])->name('approvalDecAdmin.cashadvancedDecApprovedAdmin');
        Route::post('/cashadvanced/approvalExt/submit/{id}', [ApprovalReimburseController::class, 'cashadvancedActionExtendAdmin'])->name('approvalExtAdmin.cashadvancedExtApprovedAdmin');
    });

    // Route::get('/cashadvanced/deklarasi/form/{id}', [ReimburseController::class, 'cashadvancedDeklarasi'])->name('cashadvanced.deklarasi');
    // Route::post('/cashadvanced/deklarasi/submit/{id}', [ReimburseController::class, 'cashadvancedDeclare'])->name('cashadvanced.declare');
    Route::get('/cashadvanced/request', [ReimburseController::class, 'requestCashadvanced'])->name('cashadvancedRequest');

    // My Cash Advanced Deklarasi
    Route::get('/cashadvanced/deklarasi', [ReimburseController::class, 'deklarasiCashadvanced'])->name('cashadvancedDeklarasi');
    Route::get('/cashadvanced/deklarasi/form/{id}', [ReimburseController::class, 'cashadvancedDeklarasi'])->name('cashadvanced.deklarasi');
    Route::post('/cashadvanced/deklarasi/submit/{id}', [ReimburseController::class, 'cashadvancedDeclare'])->name('cashadvanced.declare');
    Route::get('/cashadvanced/deklarasi/download/{id}', [ReimburseController::class, 'cashadvancedDownloadDeklarasi'])->name('cashadvanced.downloadDeclare');

    // My Cash Advanced Done
    Route::get('/cashadvanced/done', [ReimburseController::class, 'doneCashadvanced'])->name('cashadvancedDone');

    // My Cash Advanced Reject
    Route::get('/cashadvanced/reject', [ReimburseController::class, 'rejectCashadvanced'])->name('cashadvancedReject');

    // My Cash Advanced Extend
    Route::post('/cashadvanced/extend', [ReimburseController::class, 'cashadvancedExtend'])->name('cashadvanced.extend');

    // My Cash Advanced
    // Route::get('/exportca/excel', [ReimburseController::class, 'exportExcel'])->name('exportca.excel');
    // Route::get('/filter-ca-transactions', [ReimburseController::class, 'filterCaTransactions'])->name('filter.ca.transactions');
    Route::get('/cashadvanced/deklarasi/form/{id}', [ReimburseController::class, 'cashadvancedDeklarasi'])->name('cashadvanced.deklarasi');
    Route::post('/cashadvanced/deklarasi/submit/{id}', [ReimburseController::class, 'cashadvancedDeclare'])->name('cashadvanced.declare');

    // Approval
    Route::get('/approval', [ApprovalReimburseController::class, 'reimbursementsApproval'])->name('approvalRem');

    // Approval Reimburse
    Route::get('/approval/cashadvanced', [ApprovalReimburseController::class, 'cashadvancedApproval'])->name('approval.cashadvanced');
    Route::get('/approval/cashadvanced/form/{id}', [ApprovalReimburseController::class, 'cashadvancedFormApproval'])->name('approval.cashadvancedForm');
    Route::post('/approval/cashadvanced/submit/{id}', [ApprovalReimburseController::class, 'cashadvancedActionApproval'])->name('approval.cashadvancedApproved');

    // Approval Reimburse
    Route::get('/approval/cashadvancedDeklarasi', [ApprovalReimburseController::class, 'cashadvancedDeklarasi'])->name('approval.cashadvancedDeklarasi');
    Route::get('/approval/cashadvancedDeklarasi/form/{id}', [ApprovalReimburseController::class, 'cashadvancedFormDeklarasi'])->name('approval.cashadvancedFormDeklarasi');
    Route::post('/approval/cashadvancedDeklarasi/submit/{id}', [ApprovalReimburseController::class, 'cashadvancedActionDeklarasi'])->name('approval.cashadvancedDeclare');

    // Approval Extend
    Route::get('/approval/cashadvancedExtend', [ApprovalReimburseController::class, 'cashadvancedExtend'])->name('approval.cashadvancedExtend');
    Route::post('/approval/cashadvancedExtended', [ApprovalReimburseController::class, 'cashadvancedActionExtended'])->name('approval.cashadvancedExtended');

    // My Hotel
    Route::get('/hotel', [ReimburseController::class, 'hotel'])->name('hotel');
    Route::get('/hotel/form', [ReimburseController::class, 'hotelCreate'])->name('hotel.form');
    Route::post('/hotel/submit', [ReimburseController::class, 'hotelSubmit'])->name('hotel.submit');
    Route::get('/hotel/edit/{id}', [ReimburseController::class, 'hotelEdit'])->name('hotel.edit');
    Route::put('/hotel/update/{id}', [ReimburseController::class, 'hotelUpdate'])->name('hotel.update');
    Route::post('/hotel/delete/{id}', [ReimburseController::class, 'hotelDelete'])->name('hotel.delete');
    Route::get('/hotel/pdf/{id}', [ReimburseController::class, 'hotelExport'])->name('hotel.export');

    // My Hotel Approval
    Route::get('/hotel/approval', [ReimburseController::class, 'hotelApproval'])->name('hotel.approval');
    Route::get('/hotel/approval/detail/{id}', [ReimburseController::class, 'hotelApprovalDetail'])->name('hotel.approval.detail');
    Route::put('/hotel/status/change/{id}', [ReimburseController::class, 'updatestatusHotel'])->name('change.status.hotel');

    //Hotel Admin
    Route::middleware(['permission:report_hcis_htl'])->group(function () {
        Route::get('/hotel/admin', [ReimburseController::class, 'hotelAdmin'])->name('hotel.admin');
        Route::post('/hotel/admin/booking/{id}', [ReimburseController::class, 'hotelBookingAdmin'])->name('hotel.admin-booking');
        Route::get('/hotel/excel', [ReimburseController::class, 'exportHotelAdminExcel'])->name('hotel.excel');
        Route::get('/hotel/report', [ReimburseController::class, 'hotelAdminReport'])->name('hotel.report');
        Route::post('/hotel/admin/delete/{id}', [ReimburseController::class, 'hotelDeleteAdmin'])->name('hotel.delete.admin');
        Route::post('/hotelStatus/admin/change/{id}', [ReimburseController::class, 'updatestatusHotelAdmin'])->name('changeStatus.hotel.admin');
    });

    // My Ticket
    Route::get('/ticket', [ReimburseController::class, 'ticket'])->name('ticket');
    Route::get('/ticket/form', [ReimburseController::class, 'ticketCreate'])->name('ticket.form');
    Route::post('/ticket/submit', [ReimburseController::class, 'ticketSubmit'])->name('ticket.submit');
    Route::get('/ticket/edit/{id}', [ReimburseController::class, 'ticketEdit'])->name('ticket.edit');
    Route::put('/ticket/update/{id}', [ReimburseController::class, 'ticketUpdate'])->name('ticket.update');
    Route::post('/ticket/delete/{id}', [ReimburseController::class, 'ticketDelete'])->name('ticket.delete');
    Route::get('/ticket/pdf/{id}', [ReimburseController::class, 'ticketExport'])->name('ticket.export');

    // My Ticket Approval
    Route::get('/ticket/approval', [ReimburseController::class, 'ticketApproval'])->name('ticket.approval');
    Route::get('/ticket/approval/detail/{id}', [ReimburseController::class, 'ticketApprovalDetail'])->name('ticket.approval.detail');
    Route::put('/ticket/status/change/{id}', [ReimburseController::class, 'updatestatusTicket'])->name('change.status.ticket');


    //Ticket Admin
    Route::middleware(['permission:report_hcis_tkt'])->group(function () {
        Route::get('/ticket/admin', [ReimburseController::class, 'ticketAdmin'])->name('ticket.admin');
        Route::post('/ticket/admin/booking/{id}', [ReimburseController::class, 'ticketBookingAdmin'])->name('ticket.admin-booking');
        Route::get('/ticket/excel', [ReimburseController::class, 'exportTicketAdminExcel'])->name('ticket.excel');
        Route::get('/ticket/report', [ReimburseController::class, 'ticketAdminReport'])->name('ticket.report');
        Route::post('/ticket/admin/delete/{id}', [ReimburseController::class, 'ticketDeleteAdmin'])->name('ticket.delete.admin');
        Route::post('/ticket/admin/statusChange/{id}', [ReimburseController::class, 'updateStatusTicketAdmin'])->name('changeStatus.ticket.admin');
    });

    // My Goals
    Route::get('/goals', [MyGoalController::class, 'index'])->name('goals');
    Route::get('/goals/detail/{id}', [MyGoalController::class, 'show'])->name('goals.detail');
    Route::get('/goals/form/{id}', [MyGoalController::class, 'create'])->name('goals.form');
    Route::post('/goals/submit', [MyGoalController::class, 'store'])->name('goals.submit');
    Route::get('/goals/edit/{id}', [MyGoalController::class, 'edit'])->name('goals.edit');
    Route::post('/goals/update', [MyGoalController::class, 'update'])->name('goals.update');

    // Team Goals
    Route::get('/team-goals', [TeamGoalController::class, 'index'])->name('team-goals');
    Route::get('/team-goals/detail/{id}', [TeamGoalController::class, 'show'])->name('team-goals.detail');
    Route::get('/team-goals/form/{id}', [TeamGoalController::class, 'create'])->name('team-goals.form');
    Route::post('/team-goals/submit', [TeamGoalController::class, 'store'])->name('team-goals.submit');
    Route::get('/team-goals/edit/{id}', [TeamGoalController::class, 'edit'])->name('team-goals.edit');
    Route::get('/team-goals/approval/{id}', [TeamGoalController::class, 'approval'])->name('team-goals.approval');
    // Route::post('/goals/update', [TeamGoalController::class, 'update'])->name('goals.update');
    Route::get('/get-tooltip-content', [TeamGoalController::class, 'getTooltipContent']);
    Route::get('/units-of-measurement', [TeamGoalController::class, 'unitOfMeasurement']);

    // Approval
    // Route::post('/approval/goal', [ApprovalController::class, 'store'])->name('approval.goal');

    // Sendback
    Route::post('/sendback/goal', [SendbackController::class, 'store'])->name('sendback.goal');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');

    Route::get('export/employees', [ExportExcelController::class, 'export'])->name('export.employee');
    Route::get('/get-report-content/{reportType}', [ReportController::class, 'getReportContent']);
    Route::get('/export/report-emp', [ExportExcelController::class, 'exportreportemp'])->name('export.reportemp');

    Route::post('/export', [ExportExcelController::class, 'export'])->name('export');
    Route::get('/download-template', function () {
        // Logika untuk mengunduh template Excel uploads/proofs/01113060004/1733280403_Screenshot 2024-12-04 091727.png
        // return response()->download(public_path('files/template_import_mdc.xlsx'));
        return response()->download(Storage::path('public/uploads/template_import_mdc.xlsx'));
    })->name('download-template');
    Route::post('/admin-export', [ExportExcelController::class, 'exportAdmin'])->name('admin.export');
    Route::post('/notInitiatedReport', [ExportExcelController::class, 'notInitiated'])->name('team-goals.notInitiated');
    Route::post('/initiatedReport', [ExportExcelController::class, 'initiated'])->name('team-goals.initiated');
    // Route::get('/export/goals', [ReportController::class, 'exportGoal'])->name('export.goal');
    Route::post('/get-report-content', [ReportController::class, 'getReportContent'])->name('reports.content');

    Route::get('/changes-group-company', [ReportController::class, 'changesGroupCompany']);
    Route::get('/changes-company', [ReportController::class, 'changesCompany']);

    //Medical
    Route::get('/medical', [MedicalController::class, 'medical'])->name('medical');
    Route::get('/medical/form-add', [MedicalController::class, 'medicalForm'])->name('medical-form.add');
    Route::post('/medical/form-add/post', [MedicalController::class, 'medicalCreate'])->name('medical-form.post');
    Route::get('/medical/form-update/{id}', [MedicalController::class, 'medicalFormUpdate'])->name('medical-form.edit');
    Route::put('/medical/form-update/update/{id}', [MedicalController::class, 'medicalUpdate'])->name('medical-form.put');
    Route::delete('/medical/delete/{id}', [MedicalController::class, 'medicalDelete'])->name('medical.delete');
    Route::get('/medical/export-excel/', [MedicalController::class, 'medicalForm'])->name('export.medical');

    //Medical Admin
    Route::middleware(['permission:report_hcis_md'])->group(function () {
        Route::get('/medical/admin', [MedicalController::class, 'medicalAdmin'])->name('medical.admin');
        Route::get('/medical/report', [MedicalController::class, 'medicalReportAdmin'])->name('medical.report');
        Route::get('/medical/confirmation', [MedicalController::class, 'medicalAdminConfirmation'])->name('medical.confirmation');
        Route::get('/medical/detail/{key}', [MedicalController::class, 'medicalAdminDetail'])->name('medical.detail');
        Route::post('/medical/detail/editPlafon/{period}/{employee}', [MedicalController::class, 'medicalAdminEditPlafon'])->name('medical.edit.plafon');
        Route::get('/medical/detail/form-add-detail/{employee_id}', [MedicalController::class, 'medicalAdminDetailForm'])->name('medical-form.add-admin');
        Route::post('/medical/detail/form-add-detail/post/{employee_id}', [MedicalController::class, 'medicalAdminDetailCreate'])->name('medical-form.post-admin');
        Route::post('/medical/import-excel/', [MedicalController::class, 'importAdminExcel'])->name('import.medical');
        Route::get('/exportmed/excel', [MedicalController::class, 'exportAdminExcel'])->name('exportmed.excel');
        Route::get('/exportmed/detail/excel/{employee_id}', [MedicalController::class, 'exportDetailExcel'])->name('exportmed-detail.excel');
        Route::get('/medical/admin/table', [MedicalController::class, 'medicalAdminTable'])->name('medical-table.admin');
        Route::get('/medical/admin/form-update/{id}', [MedicalController::class, 'medicalAdminForm'])->name('medical-admin-form.edit');
        Route::put('/medical/admin/form-update/update/{id}', [MedicalController::class, 'medicalAdminUpdate'])->name('medical-admin-form.put');
        Route::delete('/medical/admin/delete/{id}', [MedicalController::class, 'medicalAdminDelete'])->name('medical-admin.delete');
        Route::delete('/medical/admin/delete/report/{id}', [MedicalController::class, 'medicalReportAdminDelete'])->name('medicalReport-admin.delete');
        Route::post('/delete-failed-import', function (Request $request) {
            $filePath = str_replace(asset('storage/'), '', $request->file_path); // Ambil path relatif

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                return response()->json(['message' => 'File deleted successfully']);
            }

            return response()->json(['message' => 'File not found'], 404);
        })->name('delete.failed.import');
    });

    //Medical Approval
    Route::get('/medical/approval', [MedicalController::class, 'medicalApproval'])->name('medical.approval');
    Route::get('/medical/approval/form-approval/{id}', [MedicalController::class, 'medicalApprovalForm'])->name('medical-approval-form.edit');
    Route::put('/medical/approval/form-approval/update/{id}', [MedicalController::class, 'medicalApprovalUpdate'])->name('medical-approval-form.put');

    //Taksi Form
    Route::get('/taksi', [TaksiController::class, 'taksi'])->name('taksi');
    Route::get('/taksi/form/add', [TaksiController::class, 'taksiFormAdd'])->name('taksi.form.add');
    Route::post('/taksi/form/post', [TaksiController::class, 'taksiCreate'])->name('taksi.form.post');

    Route::middleware(['permission:report_hcis_bt'])->group(function () {
        //ADMIN BT
        Route::get('/businessTrip/admin', [BusinessTripController::class, 'admin'])->name('businessTrip.admin');
        Route::get('/businessTrip/admin/filterDate', [BusinessTripController::class, 'filterDateAdmin'])->name('businessTrip-filterDate.admin');
        Route::put('businessTrip/status/confirm/{id}', [BusinessTripController::class, 'updatestatus'])->name('confirm.status');
        Route::get('/businessTrip/declaration/admin/{id}', [BusinessTripController::class, 'deklarasiAdmin'])->name('businessTrip.deklarasi.admin');
        Route::put('/businessTrip/declaration/admin/status/{id}', [BusinessTripController::class, 'deklarasiStatusAdmin'])->name('businessTrip.deklarasi.admin.status');
        Route::delete('/businessTrip/admin/delete/{id}', [BusinessTripController::class, 'deleteAdmin'])->name('delete.btAdmin');

        //division
        Route::get('/businessTrip/admin/division', [BusinessTripController::class, 'adminDivision'])->name('businessTrip.admin.division');
        Route::get('/admin/business-trip/filter-division', [BusinessTripController::class, 'filterDivision'])
            ->name('businessTrip-filterDivision.admin');
        Route::get('businessTrip/division/export/excel/', [BusinessTripController::class, 'exportExcelDivision'])->name('export.excel.division');
        Route::get('/businessTrip/division/export-pdf', [BusinessTripController::class, 'exportPdfDivision'])->name('export.pdf.division');
    });

    //Business Trip Travel
    Route::get('/businessTrip', [BusinessTripController::class, 'businessTrip'])->name('businessTrip');
    Route::get('/businessTrip/form/add', [BusinessTripController::class, 'businessTripformAdd'])->name('businessTrip.add');
    Route::post('/businessTrip/form/post', [BusinessTripController::class, 'businessTripCreate'])->name('businessTrip.post');
    Route::get('/businessTrip/form/update/{id}', [BusinessTripController::class, 'formUpdate'])->name('businessTrip.update');
    Route::put('/businessTrip/update/{id}', [BusinessTripController::class, 'update'])->name('update.bt');
    Route::delete('/businessTrip/delete/{id}', [BusinessTripController::class, 'delete'])->name('delete.bt');
    Route::post('/businessTrip/saveDraft', [BusinessTripController::class, 'saveDraft'])->name('businessTrip.saveDraft');

    // Extend BT and CA
    Route::post('/businessTrip/extend', [BusinessTripController::class, 'businessTripExtend'])->name('businessTrip.extend');
    Route::post('/businessTrip/approval/extend', [BusinessTripController::class, 'updateStatusExtended'])->name('businessTrip.approvalExtended');

    //pdf BT
    Route::get('/businessTrip/pdf/{id}', [BusinessTripController::class, 'pdfDownload'])->name('pdf');
    Route::post('/businessTrip/export/{id}', [BusinessTripController::class, 'export'])->name('exportbt');
    //Search
    Route::get('/search/name', [SearchController::class, 'searchNik'])->name('searchNik');
    Route::get('/search/passengers', [SearchController::class, 'searchPassenger'])->name('searchNik');
    Route::get('/search/no-sppd', [SearchController::class, 'searchSppd'])->name('searchSppd');

    //DEKLARASI BT
    Route::get('/businessTrip/declaration/{id}', [BusinessTripController::class, 'deklarasi'])->name('businessTrip.deklarasi');
    Route::put('/businessTrip/declaration/update/{id}', [BusinessTripController::class, 'deklarasiCreate'])->name('businessTrip.deklarasi.create');

    Route::get('/businessTrip/search', [BusinessTripController::class, 'search'])->name('businessTrip-search');
    Route::get('/businessTrip/filterDate', [BusinessTripController::class, 'filterDate'])->name('businessTrip-filterDate');


    //Export BT excel
    Route::get('businessTrip/export/excel/', [BusinessTripController::class, 'exportExcel'])->name('export.excel');

    //APPROVAL BT
    Route::get('/businessTrip/approval', [BusinessTripController::class, 'approval'])->name('businessTrip.approval');
    Route::get('/businessTrip/approval/detail/{id}', [BusinessTripController::class, 'approvalDetail'])->name('businessTrip.approvalDetail');
    Route::get('/businessTrip/approval/detail/declaration/{id}', [BusinessTripController::class, 'ApprovalDeklarasi'])->name('businessTrip.approvalDetail.dekalrasi');
    Route::get('/businessTrip/approval/filterDate', [BusinessTripController::class, 'filterDateApproval'])->name('businessTrip-filterDate.approval');
    Route::put('businessTrip/status/confirm/{id}', [BusinessTripController::class, 'updatestatus'])->name('confirm.status');
    Route::put('businessTrip/status/confirm/declaration/{id}', [BusinessTripController::class, 'updateStatusDeklarasi'])->name('confirm.deklarasi');
    Route::put('businessTrip/status/change/{id}', [BusinessTripController::class, 'updatestatus'])->name('change.status');

    //APPROVAL ADMIN BT
    Route::put('businessTrip/status/approve/{id}', [BusinessTripController::class, 'adminApprove'])->name('admin.approve');
    Route::put('businessTrip/status/revisi/{id}', [BusinessTripController::class, 'adminRevisi'])->name('admin.revisi');
    Route::put('businessTrip/status/reject/{id}', [BusinessTripController::class, 'adminReject'])->name('admin.reject');

    //PDF BT
    Route::get('/businessTrip/pdf/{id}', [BusinessTripController::class, 'pdfDownload'])->name('pdf');
    Route::get('/businessTrip/export/{id}/{types?}', [BusinessTripController::class, 'export'])->name('export');
    //ADMIN PDF
    Route::get('/businessTrip/pdf/admin/{id}', [BusinessTripController::class, 'pdfDownloadAdmin'])->name('pdf.admin');
    Route::get('/businessTrip/export/admin/{id}/{types?}', [BusinessTripController::class, 'exportAdmin'])->name('export.admin');

    //ADMIN Home Trip
    // Route::middleware(['permission:report_hcis_ht'])->group(function () {

    Route::get('/home-trip/admin', [HomeTripController::class, 'homeTripAdmin'])->name('home-trip.admin');
    Route::get('/home-trip/admin/detail/{key}', [HomeTripController::class, 'homeTripAdminDetail'])->name('home-trip.detail');
    // });

    //Home Trip
    Route::get('/home-trip', [HomeTripController::class, 'homeTrip'])->name('home-trip');
    Route::get('/home-trip/form-add', [HomeTripController::class, 'homeTripForm'])->name('home-trip-form.add');
    Route::post('/home-trip/form-add/post', [HomeTripController::class, 'homeTripCreate'])->name('home-trip-form.post');
    Route::post('/home-trip/delete/{id}', [HomeTripController::class, 'homeTripDelete'])->name('home-trip.delete');
    Route::get('/home-trip/form-update/{id}', [HomeTripController::class, 'homeTripFormUpdate'])->name('home-trip-form.edit');
    Route::put('/home-trip/form-update/update/{id}', [HomeTripController::class, 'homeTripUpdate'])->name('home-trip-form.put');

    //Home Trip Approval
    // HT APPROVAL ROUTE HERE !!!!!!!


    // Authentication
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('{first}/{second}', [HomeController::class, 'secondLevel'])->name('second');

    Route::get('/guides', [GuideController::class, 'index'])->name('guides');
    Route::post('/guides', [GuideController::class, 'store'])->name('upload.guide');
    Route::delete('/guides-delete/{id}', [GuideController::class, 'destroy'])->name('delete.guide');

    // ============================ Administrator ===================================


    Route::middleware(['permission:viewschedule'])->group(function () {
        // Schedule
        Route::get('/schedules', [ScheduleController::class, 'schedule'])->name('schedules');
        Route::get('/schedules-form', [ScheduleController::class, 'form'])->name('schedules.form');
        Route::post('/schedule-save', [ScheduleController::class, 'save'])->name('save-schedule');
        Route::get('/schedule/edit/{id}', [ScheduleController::class, 'edit'])->name('edit-schedule');
        Route::post('/schedule', [ScheduleController::class, 'update'])->name('update-schedule');
        Route::delete('/schedule/{id}', [ScheduleController::class, 'softDelete'])->name('soft-delete-schedule');
    });

    Route::middleware(['permission:viewlayer'])->group(function () {
        // layer
        Route::get('/layer', [LayerController::class, 'layer'])->name('layer');
        Route::post('/update-layer', [LayerController::class, 'updatelayer'])->name('update-layer');
        Route::post('/import-layer', [LayerController::class, 'importLayer'])->name('import-layer');
        Route::post('/history-show', [LayerController::class, 'show'])->name('history-show');
    });

    Route::middleware(['permission:viewrole'])->group(function () {
        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles');
        Route::post('/admin/roles/submit', [RoleController::class, 'store'])->name('roles.store');
        Route::post('/admin/roles/update', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/admin/roles/delete/{id}', [RoleController::class, 'destroy'])->name('roles.delete');
        Route::get('/admin/roles/assign', [RoleController::class, 'assign'])->name('roles.assign');
        Route::get('/admin/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::get('/admin/roles/manage', [RoleController::class, 'manage'])->name('roles.manage');
        Route::get('/admin/roles/get-assignment', [RoleController::class, 'getAssignment'])->name('getAssignment');
        Route::get('/admin/roles/get-permission', [RoleController::class, 'getPermission'])->name('getPermission');
        Route::post('/admin/assign-user', [RoleController::class, 'assignUser'])->name('assign.user');
    });

    Route::middleware(['permission:viewdesignation'])->group(function () {
        // Designation
        Route::get('/designations', [DesignationController::class, 'index'])->name('designations');

        Route::put('/admin/designations/update/dept-head/{id}', [DesignationController::class, 'updateDeptHead'])->name('designations.update.dept');

        Route::put('/admin/designations/update/director-flag/{id}', [DesignationController::class, 'updateDirectorHead'])->name('designations.update.director');
    });

    Route::middleware(['permission:viewonbehalf'])->group(function () {
        // Approval-Admin
        Route::post('/admin/approval/goal', [AdminOnBehalfController::class, 'store'])->name('admin.approval.goal');
        Route::get('/admin/approval/goal/{id}', [AdminOnBehalfController::class, 'create'])->name('admin.create.approval.goal');
        // Goals - Admin
        Route::get('/onbehalf', [AdminOnBehalfController::class, 'index'])->name('onbehalf');
        Route::post('/admin/onbehalf/content', [AdminOnBehalfController::class, 'getOnBehalfContent'])->name('admin.onbehalf.content');
        Route::post('/admin/goal-content', [AdminOnBehalfController::class, 'getGoalContent']);
        // Sendback
        Route::post('/admin/sendback/goal', [AdminSendbackController::class, 'store'])->name('admin.sendback.goal');
    });

    Route::middleware(['permission:viewreport'])->group(function () {

        Route::get('/reports-admin', [AdminReportController::class, 'index'])->name('admin.reports');
        Route::get('/admin/get-report-content/{reportType}', [AdminReportController::class, 'getReportContent']);
        Route::post('/admin/get-report-content', [AdminReportController::class, 'getReportContent']);
        Route::get('/admin/changes-group-company', [AdminReportController::class, 'changesGroupCompany']);
        Route::get('/admin/changes-company', [AdminReportController::class, 'changesCompany']);
        //Employee
        Route::get('/employees', [EmployeeController::class, 'employee'])->name('employees');
        Route::get('/employee/filter', [EmployeeController::class, 'filterEmployees'])->name('employee.filter');
    });
});


Route::fallback(function () {
    return view('errors.404');
});

require __DIR__ . '/auth.php';
