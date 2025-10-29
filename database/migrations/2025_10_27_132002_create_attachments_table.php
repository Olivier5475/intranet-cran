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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            // L'attachement appartient à UN document
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');

            $table->string('name'); // Nom du fichier attaché
            $table->string('storage_path');
            $table->string('mimetype');
            $table->unsignedBigInteger('size');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
