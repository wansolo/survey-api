<?php
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;


Route::apiResource('surveys', SurveyController::class)->except('destroy','update');
Route::post('surveys/{survey}/responses', [SurveyController::class, 'storeResponses']);
Route::get('surveys/{survey}/results', [SurveyController::class, 'showResults']);
