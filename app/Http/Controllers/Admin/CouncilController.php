<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Council;
use Illuminate\Http\Request;

/**
 * Contrôleur des séances de conseil (espace admin).
 *
 * Gère la création, l'affichage et la suppression des séances (`Council`).
 * Chaque séance regroupe des documents (délibérations, procès-verbaux)
 * traités par la pipeline OCR.
 *
 * Routes exposées : index, store, show, destroy.
 */
class CouncilController extends Controller
{
    /**
     * Liste toutes les séances avec leurs documents, par date décroissante.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $councilsByYear = Council::with(['documents' => function ($query) {
            $query->whereNull('parent_document_id')->orderBy('type')->orderBy('title');
        }, 'documents.annexes'])
            ->orderBy('council_date')
            ->get()
            ->groupBy(fn ($c) => $c->council_date->year)
            ->sortKeysDesc();

        return view('admin.councils.index', compact('councilsByYear'));
    }

    /**
     * Enregistre une nouvelle séance en base.
     *
     * Redirige vers le tableau de bord admin après création.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'council_date' => 'required|date',
        ]);

        Council::create([
            'council_date' => $request->council_date,
        ]);

        return redirect()
            ->route('admin.dashadmin')
            ->with('success', 'Séance créée avec succès.');
    }

    /**
     * Supprime une séance et redirige vers la liste.
     *
     * Les documents associés sont supprimés en cascade
     * si la contrainte de clé étrangère est configurée en base.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Council $council)
    {
        $council->delete();

        return redirect()
            ->route('admin.councils.index')
            ->with('success', 'Séance supprimée.');
    }

    /**
     * Affiche le détail d'une séance avec tous ses documents (admin).
     *
     * Les documents sont triés par type puis par titre.
     *
     * @return \Illuminate\View\View
     */
    public function show(Council $council)
    {
        $documents = $council->documents()
            ->whereNull('parent_document_id')
            ->orderBy('type')
            ->orderBy('title')
            ->with('annexes')
            ->get();

        return view('admin.councils.show', compact('council', 'documents'));
    }
}
