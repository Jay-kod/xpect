<?php

use App\Http\Controllers\AiReasoningController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\ManOfTheMatchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SystemHealthController;
use App\Http\Controllers\TodayFixturesController;
use App\Http\Controllers\TrackRecordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Xpect Football Prediction & Analytics Tool
|--------------------------------------------------------------------------
*/

// Dashboard: Fixtures, Predictions & AI Previews
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/matches/{match}/predict', [DashboardController::class, 'predict'])->name('matches.predict');
Route::post('/predictions/{prediction}/preview', [DashboardController::class, 'generatePreview'])->name('predictions.preview');

// Today's Fixtures Hub: Central Multi-League Matchday Workspace
Route::get('/today', [TodayFixturesController::class, 'index'])->name('today');
Route::post('/today/predict-all', [TodayFixturesController::class, 'predictAll'])->name('today.predict-all');

// Track Record: Historical Evaluation, Baselines & Calibration
Route::get('/track-record', [TrackRecordController::class, 'index'])->name('track-record');

// AI Reasoning Studio: Mathematical Permutation Matrix & Grounded Explainer
Route::get('/ai-reasoning', [AiReasoningController::class, 'index'])->name('ai-reasoning');
Route::post('/ai-reasoning/simulate', [AiReasoningController::class, 'simulate'])->name('ai-reasoning.simulate');
Route::post('/ai-reasoning/check-guard', [AiReasoningController::class, 'checkGuard'])->name('ai-reasoning.check-guard');

// Man of the Match (MOTM): Algorithmic Player Ratings & Historical Awards
Route::get('/motm', [ManOfTheMatchController::class, 'index'])->name('motm');
Route::post('/matches/{match}/motm', [ManOfTheMatchController::class, 'evaluate'])->name('motm.evaluate');
Route::post('/motm/evaluate-batch', [ManOfTheMatchController::class, 'batchEvaluate'])->name('motm.batch');

// System Health: Coverage Timeline, Data Cleanliness & Integrity Auditor
Route::get('/system-health', [SystemHealthController::class, 'index'])->name('system-health');
Route::post('/system-health/run', [SystemHealthController::class, 'runAudit'])->name('system-health.run');

// Settings: API Credentials, Model Config & Maintenance Operations
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/settings/keys', [SettingsController::class, 'updateKeys'])->name('settings.keys');
Route::post('/settings/test-key', [SettingsController::class, 'testKey'])->name('settings.test-key');
Route::post('/settings/action', [SettingsController::class, 'runAction'])->name('settings.action');

// Themes: Browser-local appearance preferences
Route::view('/themes', 'themes.index')->name('themes');

// Model Changelog: Version History, Benchmarks & Releases
Route::get('/changelog', [ChangelogController::class, 'index'])->name('changelog');

// Technical Documentation: System Architecture, Formulas & User Manual
Route::get('/docs', [DocsController::class, 'index'])->name('docs');
