<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Document;

/**
 * Contrôleur de l'explorateur de documents pour agents IA.
 *
 * Expose une vue structurée de l'ensemble des documents indexés,
 * organisés par type (délibérations, procès-verbaux) puis par année,
 * avec leurs annexes publiées. Destiné à fournir un contexte documentaire
 * à un agent conversationnel.
 *
 * Routes exposées : index.
 */
class AgentExplorerController extends Controller
{
    /**
     * Construit et retourne l'arbre documentaire pour l'explorateur agent.
     *
     * Récupère tous les documents publiés (status `indexed`) sans parent,
     * les groupe par type puis par année décroissante, et charge leurs annexes
     * publiées en eager loading.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $documents = Document::published()
            ->whereNull('parent_document_id')
            ->whereHas('council')
            ->with(['council', 'annexes' => fn ($q) => $q->published()])
            ->get();

        $tree = [];
        foreach (['deliberation' => 'Délibérations', 'proces_verbal' => 'Procès-verbaux'] as $type => $label) {
            $byYear = $documents
                ->where('type', $type)
                ->groupBy(fn ($doc) => $doc->council->council_date->year)
                ->sortKeysDesc();

            $tree[$type] = [
                'label' => $label,
                'years' => $byYear,
            ];
        }

        return view('agent.explorer', compact('tree', 'documents'));
    }
}