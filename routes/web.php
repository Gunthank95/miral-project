<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectSwitchController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ProjectRegistrationController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\PeriodicReportController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AdminProjectRegisterController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectCompanyController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\SCurveController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route untuk halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Route untuk Autentikasi (Publik)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- ROUTE UNTUK REGISTRASI ADMIN PROJECT (PUBLIK) ---
Route::get('/register', [AdminProjectRegisterController::class, 'showRegistrationForm'])->name('register.show');
Route::post('/register', [AdminProjectRegisterController::class, 'register'])->name('register.store');

use App\Http\Controllers\InvitationAcceptanceController;

// --- ROUTE UNTUK MENERIMA UNDANGAN (PUBLIK) ---
Route::get('/invitations/accept/{token}', [InvitationAcceptanceController::class, 'showAcceptanceForm'])->name('invitations.accept');
Route::post('/invitations/register', [InvitationAcceptanceController::class, 'processAcceptance'])->name('invitations.process_register');

// --- ROUTE UNTUK PENDAFTARAN PROYEK (setelah login) ---
Route::get('/projects/register', [ProjectRegistrationController::class, 'create'])->name('projects.register.create');
Route::post('/projects', [ProjectRegistrationController::class, 'store'])->name('projects.store');

// --- ROUTE UNTUK SUPER ADMIN (Perlu Login & Peran Super Admin) ---
Route::prefix('superadmin')->middleware(['auth', 'super.admin'])->name('superadmin.')->group(function () {
    // Rute Super Admin yang sudah ada
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/registration-tokens', [SuperAdminController::class, 'tokensIndex'])->name('tokens.index');
    Route::post('/registration-tokens', [SuperAdminController::class, 'tokensStore'])->name('tokens.store');

    // Rute untuk Data Master
    Route::get('/materials', [MasterDataController::class, 'materialsIndex'])->name('materials.index');
    Route::post('/materials', [MasterDataController::class, 'materialsStore'])->name('materials.store');
    
    // TAMBAHKAN: Route baru untuk Edit, Update, dan Hapus Material
    Route::put('/materials/{material}', [MasterDataController::class, 'materialsUpdate'])->name('materials.update');
    Route::delete('/materials/{material}', [MasterDataController::class, 'materialsDestroy'])->name('materials.destroy');

    Route::get('/work-items', [MasterDataController::class, 'workItemsIndex'])->name('work-items.index');
    Route::post('/work-items', [MasterDataController::class, 'workItemsStore'])->name('work-items.store');
    Route::get('/work-items/{work_item}/materials', [MasterDataController::class, 'workItemNeedsIndex'])->name('work-items.materials.index');
    Route::post('/work-items/{work_item}/materials', [MasterDataController::class, 'workItemNeedsStore'])->name('work-items.materials.store');
    Route::delete('/work-items/{work_item}/materials/{need}', [MasterDataController::class, 'workItemNeedsDestroy'])->name('work-items.materials.destroy');
    Route::get('/work-items/{work_item}/materials/{need}/edit', [MasterDataController::class, 'workItemNeedsEdit'])->name('work-items.materials.edit');
    Route::put('/work-items/{work_item}/materials/{need}', [MasterDataController::class, 'workItemNeedsUpdate'])->name('work-items.materials.update');
});

Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    Route::post('/materials/modal-store', [MasterDataController::class, 'materialsStoreModal'])->name('materials.store.modal');
});


// --- ROUTE LAINNYA (Perlu Login) ---
Route::middleware('auth')->group(function () {
    // TAMBAHKAN: Rute untuk Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/project/{project}/data-proyek', [\App\Http\Controllers\ProjectDataController::class, 'show'])->name('projects.data-proyek');
	
	// ROUTE UNTUK SCHEDULE
    Route::get('/package/{package}/schedule', [\App\Http\Controllers\ScheduleController::class, 'index'])->name('schedules.index');
	Route::post('/package/{package}/schedule', [\App\Http\Controllers\ScheduleController::class, 'store'])->name('schedules.store');
	Route::put('/schedule/{schedule}', [\App\Http\Controllers\ScheduleController::class, 'update'])->name('schedules.update');
	Route::post('/package/{package}/schedule/import-from-rab', [\App\Http\Controllers\ScheduleController::class, 'importFromRab'])->name('schedules.import_from_rab');
	Route::delete('/schedule/{schedule}', [\App\Http\Controllers\ScheduleController::class, 'destroy'])->name('schedules.destroy');
	Route::post('/schedule/batch-delete', [\App\Http\Controllers\ScheduleController::class, 'batchDestroy'])->name('schedules.batch_delete');
	Route::post('/schedule/update-order', [\App\Http\Controllers\ScheduleController::class, 'updateOrder'])->name('schedules.update_order');
    // TAMBAHKAN: Route untuk mengambil data schedule via web (untuk refresh)
    Route::get('/schedule-data/{package}', [\App\Http\Controllers\ScheduleController::class, 'getScheduleData'])->name('schedules.data');
    // TAMBAHKAN: Route baru untuk Kurva S
    Route::get('/package/{package}/s-curve', [SCurveController::class, 'index'])->name('s-curve.index');
	Route::post('/package/{package}/s-curve/store-plan', [SCurveController::class, 'storePlan'])->name('s-curve.store_plan');
	
	// TAMBAHKAN: Rute untuk menampilkan form edit dan memproses update data proyek
    Route::get('/project/{project}/edit-data', [\App\Http\Controllers\ProjectDataController::class, 'edit'])->name('projects.edit-data');
    Route::patch('/project/{project}/update-data', [\App\Http\Controllers\ProjectDataController::class, 'update'])->name('projects.update-data');
	
	// TAMBAHKAN: Rute untuk Manajemen Perusahaan dalam Proyek
    Route::prefix('/project/{project}/companies')->name('projects.companies.')->group(function () {
        Route::get('/create', [ProjectCompanyController::class, 'create'])->name('create');
        Route::post('/', [ProjectCompanyController::class, 'store'])->name('store');
        Route::get('/{company}/edit', [ProjectCompanyController::class, 'edit'])->name('edit');
        Route::patch('/{company}', [ProjectCompanyController::class, 'update'])->name('update');
        // Rute untuk delete bisa ditambahkan di sini nanti
    });
	
	// TAMBAHKAN: Rute untuk Manajemen Personil
    Route::prefix('/project/{project}/companies/{company}/personnel')->name('personnel.')->group(function () {
        Route::get('/create', [PersonnelController::class, 'create'])->name('create');
        Route::post('/', [PersonnelController::class, 'store'])->name('store');
        Route::get('/{personnel}/edit', [PersonnelController::class, 'edit'])->name('edit');
        Route::patch('/{personnel}', [PersonnelController::class, 'update'])->name('update');
        Route::delete('/{personnel}', [PersonnelController::class, 'destroy'])->name('destroy');
    });
	
	Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/project/{project}', [ProjectController::class, 'show'])->name('project.show');
    Route::post('/project/switch', [ProjectSwitchController::class, 'switchProject'])->name('project.switch');
    
    // RAB
    Route::get('/package/{package}/rab', [RabController::class, 'index'])->name('rab.index');
    Route::post('/package/{package}/rab/import', [RabController::class, 'import'])->name('rab.import');

    // Laporan Harian
    Route::get('/package/{package}/daily-reports', [DailyReportController::class, 'index'])->name('daily_reports.index');
	Route::get('/package/{package}', [\App\Http\Controllers\PackageController::class, 'show'])->name('package.show');
    
    Route::get('/package/{package}/daily-reports/create', [DailyReportController::class, 'create'])->name('daily_reports.create');
    Route::get('/package/{package}/daily-reports/{daily_report}/edit', [DailyReportController::class, 'edit'])->name('daily_reports.edit');
    Route::post('/daily-reports/{daily_report}/weather', [DailyReportController::class, 'storeWeather'])->name('daily_reports.weather.store');
    Route::delete('/daily-reports/weather/{weather_log}', [DailyReportController::class, 'destroyWeather'])->name('daily_reports.weather.destroy');
    Route::post('/daily-reports/{daily_report}/personnel', [DailyReportController::class, 'storePersonnel'])->name('daily_reports.personnel.store');
    Route::delete('/daily-reports/personnel/{personnel_log}', [DailyReportController::class, 'destroyPersonnel'])->name('daily_reports.personnel.destroy');
    
    // Aktivitas Pekerjaan (Daily Log)
    Route::get('/daily-reports/{daily_report}/activity/create', [DailyLogController::class, 'create'])->name('daily_log.create');
    Route::post('/package/{package}/daily-log', [DailyLogController::class, 'store'])->name('daily_log.store');
    Route::get('/activity/{daily_log}/edit', [DailyLogController::class, 'edit'])->name('daily_log.edit');
    Route::put('/activity/{daily_log}', [DailyLogController::class, 'update'])->name('daily_log.update');
    Route::delete('/activity/{daily_log}', [DailyLogController::class, 'destroy'])->name('daily_log.destroy');

    // Laporan Periodik
    Route::get('/package/{package}/periodic-reports', [PeriodicReportController::class, 'index'])->name('periodic_reports.index');
    Route::get('/package/{package}/periodic-reports/print', [PeriodicReportController::class, 'print'])->name('periodic_reports.print');

    // Manajemen Dokumen
    Route::resource('/package/{package}/documents', DocumentController::class);
	
	// --- ROUTE UNTUK MANAJEMEN PENGGUNA ---
	Route::get('/project/{project}/invitations', [InvitationController::class, 'index'])->name('invitations.index');
	Route::post('/project/{project}/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::get('/project/{project}/users', [UserController::class, 'index'])->name('users.index');
	
	// TAMBAHKAN ROUTE INI UNTUK HALAMAN PERSETUJUAN
	// Route untuk menampilkan halaman persetujuan
	Route::get('/approvals', [\App\Http\Controllers\ApprovalController::class, 'index'])->name('approvals.index');

	// TAMBAHKAN ROUTE INI untuk menyimpan hasil review dari MK
	Route::post('/approvals/{document}/review', [\App\Http\Controllers\ApprovalController::class, 'storeReview'])->name('approvals.storeReview');
	Route::post('/documents/{document}/review', [\App\Http\Controllers\DocumentController::class, 'storeReview'])->name('documents.storeReview');
	
	// Route untuk menampilkan form pengajuan Shop Drawing yang detail
	Route::get('/packages/{package}/documents/create-submission', [\App\Http\Controllers\DocumentController::class, 'createSubmission'])->name('documents.createSubmission');
	
	Route::get('/packages/{package}/documents/{document}/edit', [\App\Http\Controllers\DocumentController::class, 'edit'])->name('documents.edit');
	Route::put('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'update'])->name('documents.update');
});

// --- ROUTE UNTUK API INTERNAL (Perlu Login) ---
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/rab-item/{rab_item}/progress', [ApiController::class, 'getRabProgress'])->name('api.rab.progress');
    Route::get('/rab-items/{rab_item}/children', [ApiController::class, 'getRabItemChildren'])->name('api.rab.children');
    Route::get('/daily-reports/{daily_report}/check-activity/{rab_item}', [ApiController::class, 'checkDuplicateActivity'])->name('api.activity.check_duplicate');
    Route::get('/api/rab-item/{rab_item}/last-activity/{package}', [ApiController::class, 'getLastActivityData'])->name('api.rab.last_activity');
});

// !!! PERHATIAN: Rute ini hanya untuk sementara !!!
Route::get('/reset-superadmin-password', function () {
    $user = \App\Models\User::where('email', 'superadmin@example.com')->first();
    if ($user) {
        $user->password = \Illuminate\Support\Facades\Hash::make('password'); // Password baru Anda adalah "password"
        $user->save();
        return 'Password Super Admin berhasil di-reset ke "password". Hapus rute ini sekarang.';
    }
    return 'User superadmin@example.com tidak ditemukan.';
});