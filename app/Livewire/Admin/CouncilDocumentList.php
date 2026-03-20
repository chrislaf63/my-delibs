<?php

namespace App\Livewire\Admin;

use App\Jobs\ProcessDocumentOCR;
use App\Models\Council;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class CouncilDocumentList extends Component
{
    public int $councilId;

    public array $previousStatuses = [];

    public function mount(Council $council): void
    {
        $this->councilId = $council->id;

        // Initialiser les statuts connus au chargement (pas de toast au premier rendu)
        $documents = Document::where('council_id', $this->councilId)
            ->with('annexes')
            ->get();

        foreach ($documents as $doc) {
            $this->previousStatuses[$doc->id] = $doc->status;
            foreach ($doc->annexes as $annexe) {
                $this->previousStatuses[$annexe->id] = $annexe->status;
            }
        }
    }

    public function getHasPendingDocumentsProperty(): bool
    {
        return Document::where('council_id', $this->councilId)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }

    public function deleteDocument(int $id): void
    {
        $document = Document::findOrFail($id);
        Storage::delete($document->file_path);
        $document->delete();

        unset($this->previousStatuses[$id]);
    }

    public function reindexDocument(int $id): void
    {
        $document = Document::findOrFail($id);
        $document->update(['status' => 'pending']);
        ProcessDocumentOCR::dispatch($document);

        $this->previousStatuses[$id] = 'pending';
    }

    public function render()
    {
        $documents = Document::where('council_id', $this->councilId)
            ->whereNull('parent_document_id')
            ->with(['annexes.uploader', 'uploader'])
            ->orderBy('type')
            ->orderBy('title')
            ->get();

        // Détecter les transitions vers "indexed" et dispatcher un toast
        // On utilise concat() au lieu de prepend() pour ne pas muter la collection annexes
        foreach ($documents as $doc) {
            $this->checkAndDispatchToast($doc);
            foreach ($doc->annexes as $annexe) {
                $this->checkAndDispatchToast($annexe);
            }
        }

        return view('livewire.admin.council-document-list', [
            'documents' => $documents,
        ]);
    }

    private function checkAndDispatchToast(Document $doc): void
    {
        $prev = $this->previousStatuses[$doc->id] ?? null;
        if ($prev !== null) {
            if ($prev !== 'indexed' && $doc->status === 'indexed') {
                $this->dispatch('document-indexed', title: $doc->title);
            } elseif ($prev !== 'failed' && $doc->status === 'failed') {
                $this->dispatch('document-failed', title: $doc->title);
            }
        }
        $this->previousStatuses[$doc->id] = $doc->status;
    }
}