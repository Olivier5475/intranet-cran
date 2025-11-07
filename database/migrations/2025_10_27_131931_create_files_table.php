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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained('folders')->onDelete('cascade');

            // Où se trouve ce fichier ?
            $table->foreignId('folder_id')->constrained('folders')->onDelete('cascade');

            // Infos de stockage
            $table->string('storage_path'); // Chemin dans Storage::disk()
            $table->string('mimetype');      // "image/jpeg", "application/pdf"
            $table->unsignedBigInteger('size'); // En bytes

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
