<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PollManagementController;
use App\Http\Controllers\PollPreviewController;
use App\Http\Controllers\PollRecoveryController;
use App\Http\Controllers\PollResponseController;
use App\Http\Middleware\PrivatePollHeaders;
use Illuminate\Support\Facades\Route;

Route::view('/', 'create')->name('home');
Route::view('/opret', 'create', ['landing' => false])->name('polls.create');
Route::get('/guides', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/til', [ArticleController::class, 'index'])->name('articles.situations');
Route::get('/hjaelp', [ArticleController::class, 'index'])->name('articles.help');
Route::get('/artikler', [ArticleController::class, 'index'])->name('articles.articles');
Route::get('/sitemap.xml', [ArticleController::class, 'sitemap'])->name('sitemap');
foreach (config('articles') as $key => $article) {
    Route::get($article['path'], [ArticleController::class, 'show'])->defaults('article', $key)->name('articles.'.$key);
}
// No file extension: the host serves .png as a static file, so such a path never
// reaches PHP. See the regression test in RoutePathsTest.
Route::get('/deling/{article}', [ArticleController::class, 'preview'])
    ->whereIn('article', array_keys(config('articles')))->name('articles.preview');

Route::middleware(PrivatePollHeaders::class)->group(function () {
    Route::get('/adgang/link/{token}', [PollRecoveryController::class, 'open'])->where('token', '[a-f0-9]{64}')->middleware('throttle:60,1')->name('recovery.open');
    Route::get('/adgang/bekraeft', [PollRecoveryController::class, 'confirm'])->name('recovery.confirm');
    Route::post('/adgang/bekraeft', [PollRecoveryController::class, 'redeem'])->middleware('throttle:20,1')->name('recovery.redeem');
    Route::get('/p/{poll}/adgang', [PollRecoveryController::class, 'show'])->name('recovery.request');
    Route::post('/p/{poll}/adgang', [PollRecoveryController::class, 'request'])->middleware('throttle:recovery-mail')->name('recovery.send');
    Route::post('/p/{poll}/recovery-mail', [PollRecoveryController::class, 'register'])->middleware('throttle:recovery-mail')->name('recovery.register');
    Route::get('/p/{poll}/preview', PollPreviewController::class)->middleware('throttle:60,1')->name('polls.preview');
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
