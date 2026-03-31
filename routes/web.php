<?php

use App\Http\Controllers\Admin\CouncilController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Agent\AgentExplorerController;
use App\Http\Controllers\Public\PublicCouncilController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::get('/', [PublicCouncilController::class, 'index'])->name('public.council.index');
Route::get('/seances/{council}', [PublicCouncilController::class, 'show'])->name('public.councils.show');
Route::get('/recherche', [SearchController::class, 'index'])->name('search.index');
Route::get('/documents/{document}/download', [PublicCouncilController::class, 'download'])->name('public.documents.download');
Route::get('/documents/{document}/view', [PublicCouncilController::class, 'view'])->name('public.documents.view');

// Explorateur agent (accès public — commenter le middleware pour restreindre)
Route::get('/agent', [AgentExplorerController::class, 'index'])->name('agent.explorer');

// Espace admin
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Tableau de bord
        Route::get('/dashadmin', [AdminDashboardController::class, 'index'])->name('dashadmin');
        Route::get('/dashadmin/status', [AdminDashboardController::class, 'indexationStatus'])->name('dashadmin.status');

        // Séances
        Route::resource('councils', CouncilController::class)->only(['index', 'store', 'show', 'destroy']);

        // Documents
        Route::post('councils/{council}/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::post('documents/{document}/reindex', [DocumentController::class, 'reindex'])->name('documents.reindex');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        // Utilisateurs
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'destroy']);
    });