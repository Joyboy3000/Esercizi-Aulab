<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RevisorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'homepage'])->name('homepage');
Route::get('/set-locale/{lang}', [PublicController::class, 'setLanguage'])->name('setLocale');

Route::get('/articles', [ArticleController::class, 'index'])->name('article.index');
Route::get('/articles/create', [ArticleController::class, 'create'])->name('article.create');
Route::get('/articles/search', [ArticleController::class, 'search'])->name('article.search');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('article.show');
Route::get('/category/{category}', [ArticleController::class, 'byCategory'])->name('article.byCategory');

Route::get('/revisor/index', [RevisorController::class, 'index'])
    ->middleware('isRevisor')
    ->name('revisor.index');

Route::patch('/revisor/articles/{article}/accept', [RevisorController::class, 'accept'])
    ->middleware('isRevisor')
    ->name('revisor.accept');

Route::patch('/revisor/articles/{article}/reject', [RevisorController::class, 'reject'])
    ->middleware('isRevisor')
    ->name('revisor.reject');

Route::patch('/revisor/articles/undo', [RevisorController::class, 'undoLastReview'])
    ->middleware('isRevisor')
    ->name('revisor.undo');

Route::middleware('auth')->get('/become-revisor', [RevisorController::class, 'showBecomeRevisorForm'])
    ->name('become.revisor');

Route::middleware('auth')->post('/become-revisor', [RevisorController::class, 'becomeRevisor'])
    ->name('become.revisor.submit');

Route::get('/make-revisor/{user}', [RevisorController::class, 'makeRevisor'])->name('make.revisor');
