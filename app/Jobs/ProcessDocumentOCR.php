<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;


class ProcessDocumentOCR implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->document->update(['status' => 'processing']);

        try {

            $filePath = storage_path('app/private/' . $this->document->file_path);

            $text = $this->extractWithTika($filePath);

            if (empty(trim($text))) {
                $text = $this->extractWithTesseract($filePath);
            }

            $cleanText = preg_replace('/\s+/', ' ', $text);

            if (empty(trim($cleanText))) {
                throw new \Exception('Extraction vide');
            }

            $this->document->update([
                'content' => $cleanText,
                'status' => 'indexed',
                'indexed_at' => now(),
            ]);

        } catch (\Throwable $e) {

            $this->document->update([
                'status' => 'failed',
            ]);

            \Log::error('OCR failed for document '.$this->document->id, [
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function extractWithTika($filePath): string
    {
        // Appel HTTP vers serveur Tika
        // (à implémenter)
        return '';
    }

    protected function extractWithTesseract(string $filePath): string
    {
        $pageCount = $this->getPdfPageCount($filePath);

        if ($pageCount === 0) {
            return '';
        }

        $tempDir = sys_get_temp_dir() . '/ocr_' . uniqid();
        mkdir($tempDir);

        $text = '';

        try {
            // Traitement page par page pour limiter l'usage mémoire/disque
            for ($page = 1; $page <= $pageCount; $page++) {
                $prefix = $tempDir . '/page';

                shell_exec(sprintf(
                    'pdftoppm -r 300 -png -f %d -l %d %s %s',
                    $page,
                    $page,
                    escapeshellarg($filePath),
                    escapeshellarg($prefix)
                ));

                $images = glob($prefix . '-*.png');

                foreach ($images as $image) {
                    $text .= shell_exec(sprintf(
                        'tesseract %s stdout -l fra',
                        escapeshellarg($image)
                    )) ?? '';
                    unlink($image);
                }
            }
        } finally {
            array_map('unlink', glob($tempDir . '/*') ?: []);
            rmdir($tempDir);
        }

        return $text;
    }

    private function getPdfPageCount(string $filePath): int
    {
        $output = shell_exec(sprintf('pdfinfo %s 2>/dev/null | grep Pages', escapeshellarg($filePath)));
        if (preg_match('/Pages:\s*(\d+)/', $output ?? '', $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    public function failed(): void
    {
        $this->document->update([
            'status' => 'failed'
        ]);
    }
}
