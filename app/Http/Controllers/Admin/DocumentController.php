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


class DocumentController extends Controller
{
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

    public function storeFromDashboard(Request $request)
    {
        $validated = $request->validate([
            'council_id' => 'required|exists:councils,id',
            'type' => 'required|in:deliberation,proces_verbal,annexe',
            'title' => 'nullable|string|max:255',
            'file' => 'required|mimes:pdf',
            'parent_document_id' => 'nullable|exists:documents,id',
        ]);

        $validated['title'] ??= pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $this->handleDocumentUpload($validated, $request->file('file'));

        return redirect()
            ->route('admin.dashadmin')
            ->with('success', 'Document ajouté.');
    }

    public function destroy(Document $document)
    {
        Storage::delete($document->file_path);

        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }

    public function reindex(Document $document)
    {
        $document->update(['status' => 'pending']);

        ProcessDocumentOCR::dispatch($document);

        return back()->with('success', 'Indexation relancée.');
    }

    private function handleDocumentUpload(array $data, $file): void
    {
        $path = $file->store('documents');
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
