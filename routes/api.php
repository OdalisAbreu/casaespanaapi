<?php

use App\Http\Controllers\Api\V1\PartnersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
});

Route::get('/V1/test', [PartnersController::class, 'index']);
Route::get('/V1/test/{id}', [PartnersController::class, 'show']);
Route::post('/V1/partners', [PartnersController::class,'partners']); //Valida el Usuario en cuestion 
Route::post('/V1/newinvitation', [PartnersController::class,'store']);
Route::post('/V1/accountreport', [PartnersController::class,'accountreport']);
Route::post('/V1/invitationHistory', [PartnersController::class,'invitationHistory']);
Route::post('/V1/deleteInvitation', [PartnersController::class,'deleteInvitation']);
Route::post('/V1/updateinvitation', [PartnersController::class,'updateinvitation']);
Route::post('/V1/partnervisits', [PartnersController::class,'partnervisits']);
