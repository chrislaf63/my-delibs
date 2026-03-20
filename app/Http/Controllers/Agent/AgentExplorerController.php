<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Document;

class AgentExplorerController extends Controller
{
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