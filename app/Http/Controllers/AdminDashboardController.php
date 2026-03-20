<?php

namespace App\Http\Controllers;

use App\Models\Council;
use App\Models\Document;

/**
 * Contrôleur du tableau de bord administrateur.
 *
 * Fournit une vue d'ensemble de l'activité : statistiques globales,
 * conseils et documents récents, état de la file d'indexation OCR.
 */
class AdminDashboardController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur.
     *
     * Passe à la vue les compteurs globaux, les 5 derniers conseils et documents,
     * la liste complète des conseils (pour le formulaire d'upload rapide),
     * ainsi que les compteurs d'indexation OCR.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $totalCouncils      = Council::count();
        $totalDocuments     = Document::count();
        $totalDeliberations = Document::where('type', 'deliberation')->count();
        $totalProcesVerbaux = Document::where('type', 'proces_verbal')->count();
        $totalAnnexes       = Document::where('type', 'annexe')->count();

        $recentCouncils         = Council::latest('council_date')->take(5)->get();
        $recentIndexedDocuments = Document::with('council', 'uploader')->where('status', 'indexed')->latest('indexed_at')->take(5)->get();

        return view('admin.dashboard', array_merge(
            compact('totalCouncils', 'totalDocuments', 'totalDeliberations', 'totalProcesVerbaux', 'totalAnnexes', 'recentCouncils', 'recentIndexedDocuments'),
            $this->indexationCounts()
        ));
    }

    /**
     * Retourne les compteurs d'indexation OCR au format JSON.
     *
     * Utilisé pour rafraîchir l'état de la file d'indexation en temps réel
     * depuis le tableau de bord (polling AJAX).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexationStatus()
    {
        return response()->json($this->indexationCounts());
    }

    /**
     * Calcule les compteurs de documents par état d'indexation OCR.
     *
     * @return array{pendingDocuments: int, indexedDocuments: int, failedDocuments: int}
     */
    private function indexationCounts(): array
    {
        return [
            'pendingDocuments'    => Document::whereIn('status', ['pending', 'processing'])->count(),
            'indexedDocuments'    => Document::where('status', 'indexed')->count(),
            'failedDocuments'     => Document::where('status', 'failed')->count(),
        ];
    }
}
