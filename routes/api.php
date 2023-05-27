<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthController;

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


//Signin Public Routes
Route::post('signin',[AuthController::class,'Login']);

//Signup Public Routes
Route::post('signup',[AuthController::class,'Signup']);

//Reset Password Public Routes
Route::post('resetPassword',[UsersController::class,'resetPassword']);



//Authenticated Routes(Middleware)
Route::group(['middleware'=>'auth:sanctum'],function(){

//Send OTP Auth Route
Route::get('sendcode',[UsersController::class,'sendCode']);

//Confirm OTP Auth Route
Route::post('verifyCode',[UsersController::class,'VerifyCode']);

//Get UserInfo
Route::get('getUserInfo',[UsersController::class, 'getUserInfo']);

//Start ushescrow transaction endpoint
Route::post('pairUser',[UsersController::class, 'pairUser']);

//Get pending ushescrow transaction endpoint
Route::get('getPending',[UsersController::class, 'getPending']);

//All Request Funtionality route
Route::post('request',[UsersController::class, 'request']);

//Update Ushescrow transaction route
Route::post('updateTxn', [UsersController::class, 'updateTxn']);

//Update Ushescrow transaction route
Route::get('logout', [AuthController::class, 'logout']);

//Red Route
Route::get('red', [UsersController::class, 'red']);

//MileStone Upate Route
Route::post('updateMilestone', [UsersController::class, 'updateMilestone']);

//Update User Info
Route::post('UpdateDatas', [AuthController::class, 'UpdateDatas']);


//List of Referral
Route::get('list', [UsersController::class, 'refList']);

//Deposit Endpoint
Route::post('paidBtf', [UsersController::class, 'paidBtf']);

//Deposit Endpoint
Route::post('stats', [UsersController::class, 'stats']);

//Report Endpoint
Route::post('report', [UsersController::class, 'report']);

//Request Payment Endpoint
Route::post('payment', [UsersController::class, 'requestPayment']);

//Request Payment Endpoint
Route::post('topUp', [UsersController::class, 'topUp']);
});
