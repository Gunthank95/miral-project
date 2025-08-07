<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectSwitchController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\DailyReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

	Route::post('/project/switch', [ProjectSwitchController::class, 'switchProject'])->name('project.switch');

// --- ROUTE UNTUK ADMIN ---
	Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/materials', [MasterDataController::class, 'materialsIndex'])->name('materials.index');
    Route::post('/materials', [MasterDataController::class, 'materialsStore'])->name('materials.store');
	Route::post('/materials/modal-store', [MasterDataController::class, 'materialsStoreModal'])->name('materials.store.modal');
	
	// ... (route materials yang sudah ada) ...
    Route::get('/work-items', [MasterDataController::class, 'workItemsIndex'])->name('work-items.index');
    Route::post('/work-items', [MasterDataController::class, 'workItemsStore'])->name('work-items.store');
	
	// Route untuk mengelola kebutuhan material per item pekerjaan
	Route::get('/work-items/{work_item}/materials', [MasterDataController::class, 'workItemNeedsIndex'])->name('work-items.materials.index');
	Route::post('/work-items/{work_item}/materials', [MasterDataController::class, 'workItemNeedsStore'])->name('work-items.materials.store');
	
	// ... route post work-items.materials.store yang sudah ada ...
	Route::delete('/work-items/{work_item}/materials/{need}', [MasterDataController::class, 'workItemNeedsDestroy'])->name('work-items.materials.destroy');
	
	// ... route delete yang baru saja ditambahkan ...
	Route::get('/work-items/{work_item}/materials/{need}/edit', [MasterDataController::class, 'workItemNeedsEdit'])->name('work-items.materials.edit');
	Route::put('/work-items/{work_item}/materials/{need}', [MasterDataController::class, 'workItemNeedsUpdate'])->name('work-items.materials.update');	
});

// Route untuk halaman utama
	Route::get('/', function () {
    return view('welcome');
});

// Route untuk Autentikasi (Login & Logout)
	Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
	Route::post('/login', [LoginController::class, 'login']);
	Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// Route untuk Dashboard
// Ini adalah satu-satunya route yang kita butuhkan untuk dashboard.
	Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');
	
	Route::get('/project/{project}', [App\Http\Controllers\ProjectController::class, 'show'])
    ->middleware('auth')
    ->name('project.show');
	
	Route::get('/package/{package}/rab', [RabController::class, 'index'])->name('rab.index');

	Route::post('/package/{package}/rab/import', [RabController::class, 'import'])->name('rab.import');

	Route::get('/daily-reports/{daily_report}/activity/create', [DailyLogController::class, 'create'])->name('daily_log.create');
	Route::post('/package/{package}/daily-log', [DailyLogController::class, 'store'])->name('daily_log.store');
	
	Route::get('/api/rab-item/{rab_item}/progress', [ApiController::class, 'getRabProgress'])->middleware('auth')->name('api.rab.progress');
	
	Route::get('/package/{package}/daily-reports', [DailyReportController::class, 'index'])->name('daily_reports.index');
	
	Route::get('/package/{package}/daily-reports/create', [DailyReportController::class, 'create'])->name('daily_reports.create');
	Route::post('/package/{package}/daily-reports', [DailyReportController::class, 'store'])->name('daily_reports.store');
	
	Route::get('/package/{package}/daily-reports/{daily_report}/edit', [DailyReportController::class, 'edit'])->name('daily_reports.edit');
	
	Route::post('/daily-reports/{daily_report}/weather', [DailyReportController::class, 'storeWeather'])->name('daily_reports.weather.store');
	Route::delete('/daily-reports/weather/{weather_log}', [DailyReportController::class, 'destroyWeather'])->name('daily_reports.weather.destroy');
	
	Route::post('/daily-reports/{daily_report}/personnel', [DailyReportController::class, 'storePersonnel'])->name('daily_reports.personnel.store');
	Route::delete('/daily-reports/personnel/{personnel_log}', [DailyReportController::class, 'destroyPersonnel'])->name('daily_reports.personnel.destroy');
	
	Route::get('/api/rab-items/{rab_item}/children', [ApiController::class, 'getRabItemChildren'])->middleware('auth')->name('api.rab.children');
	
	Route::get('/api/daily-reports/{daily_report}/check-activity/{rab_item}', [ApiController::class, 'checkDuplicateActivity'])->middleware('auth')->name('api.activity.check_duplicate');
	
	Route::get('/api/rab-item/{rab_item}/last-activity/{package}', [ApiController::class, 'getLastActivityData'])->middleware('auth')->name('api.rab.last_activity');
	
	Route::get('/activity/{daily_log}/edit', [DailyLogController::class, 'edit'])->name('daily_log.edit');
	Route::put('/activity/{daily_log}', [DailyLogController::class, 'update'])->name('daily_log.update');
