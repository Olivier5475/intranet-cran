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
        Schema::table('documents', function (Blueprint $table) {
            // 'type' permettra de filtrer ou d'afficher un badge "Appel à projet" dans ton front
            $table->string('type')->default('document')->after('id');

            // La deadline doit être nullable car les documents classiques n'en ont pas
            $table->dateTime('deadline')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['type', 'deadline']);
        });
    }
};
