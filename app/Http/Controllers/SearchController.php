<?php

namespace App\Http\Controllers;

use App\Models\Council;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q'           => 'nullable|string|max:255',
            'type'        => 'nullable|in:deliberation,proces_verbal',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date',
            'council_id'  => 'nullable|integer|exists:councils,id',
            'document_id' => 'nullable|integer|exists:documents,id',
        ]);

        $q          = $request->input('q');
        $type       = $request->input('type');
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');
        $councilId  = $request->input('council_id');
        $documentId = $request->input('document_id');

        $hasFilters = (bool) ($q || $type || $dateFrom || $dateTo || $councilId || $documentId);

        $documents = Document::query()
            ->where('status', 'indexed')
            ->when($type, fn ($query) => $query->where(function ($q) use ($type) {
                $q->where('type', $type)
                    ->orWhere(function ($q2) use ($type) {
                        $q2->where('type', 'annexe')
                            ->whereHas('parent', fn ($p) => $p->where('type', $type));
                    });
            }))
            ->when($documentId, fn ($query) => $query->where('id', $documentId))
            ->when($councilId, fn ($query) => $query->where('council_id', $councilId))
            ->when($dateFrom, fn ($query) =>
                $query->whereHas('council', fn ($q2) => $q2->where('council_date', '>=', $dateFrom))
            )
            ->when($dateTo, fn ($query) =>
                $query->whereHas('council', fn ($q2) => $q2->where('council_date', '<=', $dateTo))
            )
            ->when($q, function ($query) use ($q) {
                if (DB::getDriverName() === 'sqlite') {
                    $query->where(function ($q2) use ($q) {
                        $q2->where('title', 'LIKE', "%{$q}%")
                            ->orWhere('content', 'LIKE', "%{$q}%");
                    });
                } else {
                    $phrase = str_contains($q, ' ') ? '"' . $q . '"' : $q;
                    $query->whereRaw("MATCH(title, content) AGAINST(? IN BOOLEAN MODE)", [$phrase])
                        ->selectRaw("documents.*, MATCH(title, content) AGAINST(?) AS relevance", [$phrase])
                        ->orderByDesc('relevance');
                }
            })
            ->when(! $q, fn ($query) => $query->orderByDesc('created_at'))
            ->with('council')
            ->paginate(15)
            ->withQueryString();

        $highlightedTitles = [];
        $contentSnippets = [];

        if ($q) {
            foreach ($documents as $doc) {
                $highlightedTitles[$doc->id] = $this->highlight($doc->title, $q);
                $contentSnippets[$doc->id] = $doc->content
                    ? $this->extractSnippets($doc->content, $q)
                    : [];
            }
        }

        $councils     = Council::orderByDesc('council_date')->get();
        $allDocuments = Document::where('status', 'indexed')
            ->orderBy('title')
            ->select('id', 'title', 'council_id')
            ->get();

        return view('public.search', compact(
            'documents', 'q', 'type', 'highlightedTitles', 'contentSnippets',
            'hasFilters', 'dateFrom', 'dateTo',
            'councilId', 'documentId', 'councils', 'allDocuments'
        ));
    }

    /**
     * Normalise une chaîne pour la comparaison insensible aux accents et à la casse.
     * Décompose les caractères accentués (NFD) puis supprime les marques diacritiques.
     */
    private function fold(string $s): string
    {
        if (class_exists(\Normalizer::class)) {
            $s = \Normalizer::normalize($s, \Normalizer::NFD);
            $s = preg_replace('/\p{Mn}/u', '', $s);
        }

        return mb_strtolower($s);
    }

    /**
     * Retourne le texte avec toutes les occurrences du terme enveloppées dans <mark>.
     * La recherche est insensible à la casse et aux accents.
     */
    private function highlight(string $text, string $query): string
    {
        $foldedText  = $this->fold($text);
        $foldedQuery = $this->fold($query);
        $queryLen    = mb_strlen($query);

        $result = '';
        $cursor = 0;
        $offset = 0;

        while (($pos = mb_stripos($foldedText, $foldedQuery, $offset)) !== false) {
            $result .= e(mb_substr($text, $cursor, $pos - $cursor));
            $result .= '<mark class="bg-yellow-200 rounded px-0.5">'
                . e(mb_substr($text, $pos, $queryLen))
                . '</mark>';
            $cursor = $pos + $queryLen;
            $offset = $cursor;
        }

        $result .= e(mb_substr($text, $cursor));

        return $result;
    }

    /**
     * Extrait jusqu'à $max extraits (snippets) non-chevauchants du contenu,
     * centrés sur les occurrences du terme recherché, avec le terme surligné.
     * La recherche est insensible à la casse et aux accents.
     *
     * @return string[]
     */
    private function extractSnippets(string $content, string $query, int $max = 9): array
    {
        $foldedContent = $this->fold($content);
        $foldedQuery   = $this->fold($query);
        $queryLen      = mb_strlen($query);

        $snippets = [];
        $offset   = 0;
        $lastEnd  = -1;

        while (count($snippets) < $max) {
            $pos = mb_stripos($foldedContent, $foldedQuery, $offset);
            if ($pos === false) break;

            $start = max(0, $pos - 80);

            if ($start > $lastEnd) {
                $raw          = mb_substr($content, $start, 200);
                $posInSnippet = $pos - $start;

                $highlighted = e(mb_substr($raw, 0, $posInSnippet))
                    . '<mark class="bg-yellow-200 rounded px-0.5">'
                    . e(mb_substr($raw, $posInSnippet, $queryLen))
                    . '</mark>'
                    . e(mb_substr($raw, $posInSnippet + $queryLen));

                $snippets[] = ($start > 0 ? '…' : '') . $highlighted . '…';
                $lastEnd    = $start + 200;
            }

            $offset = $pos + mb_strlen($foldedQuery);
        }

        return $snippets;
    }
}
