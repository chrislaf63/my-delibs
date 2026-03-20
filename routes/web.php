<?php

use App\Http\Controllers\Admin\CouncilController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Agent\AgentExplorerController;
use App\Http\Controllers\Public\PublicCouncilController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/welcome', function () {return view('welcome');});
Route::get('/', [PublicCouncilController::class, 'index'])->name('public.council.index');
Route::get('/seances/{council}', [PublicCouncilController::class, 'show'])->name('public.councils.show');
Route::get('/recherche', [SearchController::class, 'index'])->name('search.index');
Route::get('/documents/{document}/download', [PublicCouncilController::class, 'download'])
    ->name('public.documents.download');
Route::get('/documents/{document}/view', [PublicCouncilController::class, 'view'])
    ->name('public.documents.view');

Route::prefix('agent')
    ->name('agent.')
    // ->middleware(['auth', 'verified'])  // À décommenter pour restreindre l'accès aux agents authentifiés
    ->group(function () {
        Route::get('/', [AgentExplorerController::class, 'index'])->name('explorer');
    });

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashadmin', [AdminDashboardController::class, 'index'])->name('dashadmin');
        Route::get('/dashadmin/status', [AdminDashboardController::class, 'indexationStatus'])->name('dashadmin.status');
        Route::post('/documents/{document}/reindex', [DocumentController::class, 'reindex'])
            ->name('documents.reindex');
        Route::post('/dashboard/councils', [CouncilController::class, 'store'])
            ->name('dashboard.councils.store');
        Route::post('/dashboard/documents', [DocumentController::class, 'storeFromDashboard'])
            ->name('dashboard.documents.store');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::resource('councils', CouncilController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::post('councils/{council}/documents',
            [DocumentController::class, 'store']
        )->name('documents.store');
        Route::delete(
            'documents/{document}',
            [DocumentController::class, 'destroy']
        )->name('documents.destroy');
    });
