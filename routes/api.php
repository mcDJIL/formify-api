<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\QuestionsController;
use App\Http\Controllers\ResponsesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('v1/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('v1/auth/logout', [AuthController::class, 'logout']);

    Route::post('v1/forms', [FormsController::class, 'store']);
    Route::get('v1/forms', [FormsController::class, 'index']);
    Route::get('v1/forms/{form_slug}', [FormsController::class, 'show']);

    Route::post('v1/forms/{form_slug}/questions', [QuestionsController::class, 'store']);
    Route::delete('v1/forms/{form_slug}/questions/{question_id}', [QuestionsController::class, 'destroy']);
    
    Route::post('v1/forms/{form_slug}/responses', [ResponsesController::class, 'store']);
    Route::get('v1/forms/{form_slug}/responses', [ResponsesController::class, 'index']);
});