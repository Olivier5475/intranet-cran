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
        Schema::table('groupes', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('groupes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groupes', function (Blueprint $table) {
            // On supprime d'abord la contrainte de clé étrangère
            $table->dropForeign(['parent_id']);
            // Ensuite on peut supprimer la colonne
            $table->dropColumn('parent_id');
        });
    }
};
