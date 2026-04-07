<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            // 1. On renomme la colonne
            $table->renameColumn('isDelete', 'is_archived');
        });

        // 2. On nettoie les données existantes (NULL -> false)
        // pour éviter les erreurs lors du passage en NOT NULL
        DB::table('folders')->whereNull('is_archived')->update(['is_archived' => false]);

        Schema::table('folders', function (Blueprint $table) {
            // 3. On change le type : boolean, NOT NULL, default false
            $table->boolean('is_archived')->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->renameColumn('is_archived', 'isDelete');
            $table->boolean('isDelete')->nullable()->default(null)->change();
        });
    }
};
