<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Council;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicCouncilController extends Controller
{
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

    public function download(Document $document)
    {
        abort_unless($document->status === 'indexed', 404);

        return Storage::download(
            $document->file_path,
            $document->original_filename
        );
    }

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
