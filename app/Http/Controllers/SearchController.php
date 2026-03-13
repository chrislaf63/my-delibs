<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $type = $request->input('type');

        $documents = Document::query()
            ->where('status', 'indexed')
            ->when($type, fn ($query) =>
            $query->where('type', $type)
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

        return view('public.search', compact('documents', 'q', 'type', 'highlightedTitles', 'contentSnippets'));
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
    private function extractSnippets(string $content, string $query, int $max = 3): array
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