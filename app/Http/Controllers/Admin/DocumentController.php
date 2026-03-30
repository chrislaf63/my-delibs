<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDocumentOCR;
use App\Models\Council;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


/**
 * Contrôleur des documents (espace admin).
 *
 * Gère l'upload, la suppression et la ré-indexation des documents PDF
 * (délibérations, procès-verbaux, annexes) attachés à une séance (`Council`).
 * Chaque upload déclenche la compression Ghostscript puis le job OCR asynchrone.
 *
 * Routes exposées : store, destroy, reindex.
 */
class DocumentController extends Controller
{
    /**
     * Enregistre un document uploadé depuis la page de détail d'une séance.
     *
     * Valide le fichier PDF, construit un chemin de stockage unique,
     * compresse le PDF si activé, crée l'entrée en base et dispatch le job OCR.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Council $council)
    {
        $validated = $request->validate([
            'type' => 'required|in:deliberation,proces_verbal,annexe',
            'title' => 'nullable|string|max:255',
            'file' => 'required|mimes:pdf',
            'parent_document_id' => 'nullable|exists:documents,id',
        ]);

        $validated['council_id'] = $council->id;
        $validated['title'] ??= pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $this->handleDocumentUpload($validated, $request->file('file'));

        return back()->with('success', 'Document envoyé pour indexation.');
    }


    /**
     * Supprime un document : retire le fichier du stockage et l'entrée en base.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Document $document)
    {
        Storage::delete($document->file_path);

        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }

    /**
     * Remet le document en file d'attente pour une nouvelle indexation OCR.
     *
     * Repasse le statut à `pending` et re-dispatche le job `ProcessDocumentOCR`.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reindex(Document $document)
    {
        $document->update(['status' => 'pending']);

        ProcessDocumentOCR::dispatch($document);

        return back()->with('success', 'Indexation relancée.');
    }

    /**
     * Calcule un chemin de stockage unique pour le fichier uploadé.
     *
     * Slugifie le nom d'origine et incrémente un compteur suffixé (`-1`, `-2`…)
     * si le fichier existe déjà dans `documents/`.
     */
    private function buildStoragePath(\Illuminate\Http\UploadedFile $file): string
    {
        $nameWithoutExt = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($nameWithoutExt) ?: 'document';

        // Sécurité : s'assurer qu'il n'y a pas de traversée de chemin
        if ($slug !== basename($slug)) {
            $slug = 'document';
        }

        $filename = $slug . '.pdf';
        $path = 'documents/' . $filename;

        $counter = 1;
        while (Storage::exists($path)) {
            $filename = $slug . '-' . $counter . '.pdf';
            $path = 'documents/' . $filename;
            $counter++;
        }

        return $path;
    }

    /**
     * Orchestre l'upload complet d'un document.
     *
     * Stocke le fichier, compresse le PDF, crée l'entrée `Document` en base
     * (statut `pending`) et dispatch le job OCR.
     *
     * @param  array  $data  Champs validés (council_id, type, title, parent_document_id…)
     * @param  \Illuminate\Http\UploadedFile  $file
     */
    private function handleDocumentUpload(array $data, $file): void
    {
        $storagePath = $this->buildStoragePath($file);
        $path = $file->storeAs(dirname($storagePath), basename($storagePath));
        $absolutePath = storage_path('app/private/' . $path);

        $this->compressPdf($absolutePath);

        $document = Document::create([
            'council_id'         => $data['council_id'],
            'uploaded_by'        => auth()->id(),
            'parent_document_id' => $data['parent_document_id'] ?? null,
            'type'               => $data['type'],
            'title'              => $data['title'],
            'file_path'          => $path,
            'original_filename'  => $file->getClientOriginalName(),
            'mime_type'          => $file->getMimeType(),
            'file_size'          => filesize($absolutePath),
            'status'             => 'pending',
        ]);

        ProcessDocumentOCR::dispatch($document);
    }

    /**
     * Compresse un PDF via Ghostscript si la compression est activée en config.
     *
     * Remplace le fichier original uniquement si le fichier compressé est plus léger.
     * En cas d'absence de `gs` ou d'échec, un avertissement est loggé et le fichier
     * original est conservé intact.
     *
     * @param  string  $absolutePath  Chemin absolu vers le fichier PDF à compresser.
     */
    private function compressPdf(string $absolutePath): void
    {
        if (! config('pdf.compress_enabled')) {
            return;
        }

        $compressedPath = $absolutePath . '.compressed';
        $quality = config('pdf.compress_quality', '/ebook');

        try {
            $result = Process::run([
                'gs',
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                '-dPDFSETTINGS=' . $quality,
                '-dNOPAUSE',
                '-dQUIET',
                '-dBATCH',
                '-sOutputFile=' . $compressedPath,
                $absolutePath,
            ]);
        } catch (\Throwable $e) {
            Log::warning('PDF compression unavailable (gs not found?)', ['error' => $e->getMessage()]);
            return;
        }

        if (! $result->successful() || ! file_exists($compressedPath)) {
            Log::warning('PDF compression failed', [
                'file' => $absolutePath,
                'error' => $result->errorOutput(),
            ]);
            return;
        }

        $originalSize = filesize($absolutePath);
        $compressedSize = filesize($compressedPath);

        if ($compressedSize >= $originalSize) {
            unlink($compressedPath);
            Log::info('PDF compression skipped (no gain)', [
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
            ]);
            return;
        }

        rename($compressedPath, $absolutePath);

        Log::info('PDF compressed', [
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'reduction_pct' => round((1 - $compressedSize / $originalSize) * 100) . '%',
        ]);
    }
}
