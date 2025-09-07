<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;

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
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
	return $request->user();
		
	Route::get('/schedule-data/{package}', [ScheduleController::class, 'getScheduleData']);
	Route::get('/packages/{package}/main-rab-items', [\App\Http\Controllers\ApiController::class, 'getMainRabItems']);
	Route::get('/documents/{document}/review-details', [\App\Http\Controllers\ApiController::class, 'getDocumentReviewDetails'])->name('api.documents.review_details');
});


