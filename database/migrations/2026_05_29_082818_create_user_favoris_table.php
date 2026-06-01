<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favoris', function (Blueprint $table) {
            $table->id();

            // L'utilisateur qui met en favori (lié à ta table users)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Les deux champs pour la ressource (id et type)
            // L'équivalent rapide Laravel de ça est : $table->morphs('ressource');
            $table->unsignedBigInteger('ressource_id');
            $table->string('ressource_type');

            $table->timestamps();

            // SÉCURITÉ : Un user ne peut avoir qu'une seule fois la même ressource en favori
            $table->unique(['user_id', 'ressource_id', 'ressource_type'], 'unique_user_favori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favoris');
    }
};
