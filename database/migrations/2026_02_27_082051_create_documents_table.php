<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('type', [
                'deliberation',
                'proces_verbal'
            ])->index();
            $table->string('title');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type')->default('application/pdf');
            $table->unsignedBigInteger('file_size');
            $table->longText('content')->nullable();
            $table->enum('status', [
                'pending',
                'processing',
                'indexed',
                'failed',
            ])->default('pending')->index();
            $table->timestamp(('indexed_at'))->nullable();
            $table->timestamps();
        });

        // FULLTEXT index
        DB::statement('ALTER TABLE documents ADD FULLTEXT fulltext_content (title, content)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
