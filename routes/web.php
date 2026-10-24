<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/account/profile', [AuthController::class, 'showProfile'])->name('account.profile.edit');
    Route::put('/account/profile', [AuthController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AuthController::class, 'updatePassword'])->name('account.password.update');
    Route::get('/dashboard', [PortfolioController::class, 'dashboard'])->name('dashboard');
    Route::post('/portfolios', [PortfolioController::class, 'store'])->name('portfolio.store');
    Route::get('/portfolios/{id}/edit', [PortfolioController::class, 'edit'])->name('portfolio.edit');
    Route::get('/portfolios/{id}/download', [PortfolioController::class, 'download'])->name('portfolio.download');
    Route::get('/portfolios/{id}/resume-preview', [PortfolioController::class, 'resumePreview'])->name('portfolio.resume-preview');
    Route::put('/portfolios/{id}', [PortfolioController::class, 'update'])->name('portfolio.update');
    Route::post('/portfolios/{id}/clone', [PortfolioController::class, 'clone'])->name('portfolio.clone');
    Route::post('/portfolios/{id}/publish', [PortfolioController::class, 'togglePublish'])->name('portfolio.publish');
    Route::delete('/portfolios/{id}', [PortfolioController::class, 'destroy'])->name('portfolio.destroy');
});

Route::get('/p/{slug}', [PortfolioController::class, 'publicPortfolio'])->name('portfolio.public');
Route::get('/p/{slug}/resume', [PortfolioController::class, 'resume'])->name('portfolio.resume');
