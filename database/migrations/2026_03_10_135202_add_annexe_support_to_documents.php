<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('deliberation', 'proces_verbal', 'annexe') NOT NULL");

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('parent_document_id')
                ->nullable()
                ->after('council_id')
                ->constrained('documents')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['parent_document_id']);
            $table->dropColumn('parent_document_id');
        });

        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('deliberation', 'proces_verbal') NOT NULL");
    }
};
