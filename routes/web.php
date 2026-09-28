<?php

use App\Http\Controllers\PollController;
use App\Http\Controllers\PollManagementController;
use App\Http\Controllers\PollPreviewController;
use App\Http\Controllers\PollRecoveryController;
use App\Http\Controllers\PollResponseController;
use App\Http\Middleware\PrivatePollHeaders;
use Illuminate\Support\Facades\Route;

Route::view('/', 'create')->name('home');
Route::view('/opret', 'create')->name('polls.create');

Route::middleware(PrivatePollHeaders::class)->group(function () {
    Route::get('/adgang/link/{token}', [PollRecoveryController::class, 'open'])->where('token', '[a-f0-9]{64}')->middleware('throttle:60,1')->name('recovery.open');
    Route::get('/adgang/bekraeft', [PollRecoveryController::class, 'confirm'])->name('recovery.confirm');
    Route::post('/adgang/bekraeft', [PollRecoveryController::class, 'redeem'])->middleware('throttle:20,1')->name('recovery.redeem');
    Route::get('/p/{poll}/adgang', [PollRecoveryController::class, 'show'])->name('recovery.request');
    Route::post('/p/{poll}/adgang', [PollRecoveryController::class, 'request'])->middleware('throttle:recovery-mail')->name('recovery.send');
    Route::post('/p/{poll}/recovery-mail', [PollRecoveryController::class, 'register'])->middleware('throttle:recovery-mail')->name('recovery.register');
    Route::get('/p/{poll}/preview.png', PollPreviewController::class)->middleware('throttle:60,1')->name('polls.preview');
    Route::get('/p/{poll}/administrer', [PollManagementController::class, 'show'])->name('polls.manage');
    Route::post('/p/{poll}/administrer/{action}', [PollManagementController::class, 'update'])
        ->whereIn('action', ['add', 'remove', 'finalize', 'reopen', 'close'])->name('polls.manage.update');
    Route::post('/p/{poll}/responses', [PollResponseController::class, 'store'])->name('polls.responses');
    Route::get('/p/{poll}/results', [PollResponseController::class, 'results'])->name('polls.results');
    Route::get('/p/{poll}/del', [PollController::class, 'share'])->name('polls.share');
    Route::get('/p/{poll}', [PollController::class, 'show'])->name('polls.show');
    Route::get('/admin/{token}', [PollController::class, 'admin'])
        ->where('token', '[a-f0-9]{64}')->name('polls.admin');
});
