<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Middleware 'auth:sanctum' akan melindungi semua route di dalam grup ini
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // =================================================================
    // PINDAHKAN ROUTE LAINNYA KE SINI, DI LUAR CLOSURE /user
    // =================================================================
    Route::get('/schedule-data/{package}', [ScheduleController::class, 'getScheduleData']);
    Route::get('/packages/{package}/main-rab-items', [ApiController::class, 'getMainRabItems']);
	Route::get('/rab-item/{rab_item}/last-activity/{package}', [ApiController::class, 'getLastActivityData'])->name('api.rab.last_activity'); 

	Route::get('/rab-items/{rab_item}/children', [ApiController::class, 'getRabItemChildren'])->name('api.rab.children');	
	
    Route::get('/documents/{document}/review-details', 
               [ApiController::class, 'getReviewDetails']) // <-- UBAH INI
               ->name('api.documents.review_details');
			   
	Route::get('/notifications', [ApiController::class, 'getUnreadNotifications'])->name('api.notifications.index');
	Route::post('/notifications/{notification}/read', [ApiController::class, 'markAsRead'])->name('api.notifications.read');
			   

});


