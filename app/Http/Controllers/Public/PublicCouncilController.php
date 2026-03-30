<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Council;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Contrôleur public des séances et documents.
 *
 * Expose la consultation publique des séances de conseil et de leurs documents
 * (délibérations, procès-verbaux, annexes). Seuls les documents avec le statut
 * `indexed` sont visibles et téléchargeables.
 *
 * Routes exposées : index, show, download, view.
 */
class PublicCouncilController extends Controller
{
    /**
     * Affiche la liste publique de toutes les séances, groupées par année.
     *
     * Charge en eager loading les documents publiés (hors annexes) et leurs annexes,
     * triés par type puis par titre.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $councilsByYear = Council::with(['documents' => function ($query) {
            $query->published()->whereNull('parent_document_id')->orderBy('type')->orderBy('title');
        }, 'documents.annexes' => function ($query) {
            $query->published();
        }])
            ->orderByDesc('council_date')
            ->get()
            ->groupBy(fn ($council) => $council->council_date->year);

        return view('public.councils.index', compact('councilsByYear'));
    }

    /**
     * Affiche le détail public d'une séance avec ses délibérations et procès-verbaux.
     *
     * Sépare les documents publiés par type pour faciliter l'affichage en vue.
     * Les annexes des délibérations sont chargées en eager loading.
     *
     * @return \Illuminate\View\View
     */
    public function show(Council $council)
    {
        $documents = $council->documents()
            ->published()
            ->orderBy('type')
            ->get();

        $deliberations = $council->documents()->published()
            ->where('type', 'deliberation')
            ->with(['annexes' => fn ($q) => $q->published()])
            ->get();

        $procesVerbaux = $council->documents()->published()
            ->where('type', 'proces_verbal')
            ->get();

        return view('public.councils.show', compact('council', 'documents', 'procesVerbaux', 'deliberations'));
    }

    /**
     * Déclenche le téléchargement du PDF d'un document.
     *
     * Retourne une 404 si le document n'est pas encore indexé.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Document $document)
    {
        abort_unless($document->status === 'indexed', 404);

        return Storage::download(
            $document->file_path,
            $document->original_filename
        );
    }

    /**
     * Retourne le PDF pour affichage inline dans le navigateur.
     *
     * Retourne une 404 si le document n'est pas encore indexé.
     * Diffère de `download()` par l'en-tête `Content-Disposition: inline`.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function view(Document $document)
    {
        abort_unless($document->status === 'indexed', 404);

        return Storage::response(
            $document->file_path,
            $document->original_filename,
            ['Content-Disposition' => 'inline; filename="' . $document->original_filename . '"']
        );
    }
}
