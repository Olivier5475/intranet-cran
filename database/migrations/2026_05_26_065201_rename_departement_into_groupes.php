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
        // 1. Renommer la table principale
        Schema::rename('departements', 'groupes');

        // 2. Renommer la table pivot departement_document -> document_groupe
        if (Schema::hasTable('departement_document')) {
            Schema::rename('departement_document', 'document_groupe');
            Schema::table('document_groupe', function (Blueprint $table) {
                $table->renameColumn('departement_id', 'groupe_id');
            });
        }

        // 3. Renommer la table pivot departement_file -> file_groupe
        if (Schema::hasTable('departement_file')) {
            Schema::rename('departement_file', 'file_groupe');
            Schema::table('file_groupe', function (Blueprint $table) {
                $table->renameColumn('departement_id', 'groupe_id');
            });
        }

        // 4. Renommer la table pivot departement_folder -> folder_groupe
        if (Schema::hasTable('departement_folder')) {
            Schema::rename('departement_folder', 'folder_groupe');
            Schema::table('folder_groupe', function (Blueprint $table) {
                $table->renameColumn('departement_id', 'groupe_id');
            });
        }

        // 5. Renommer la table pivot departement_user -> groupe_user
        if (Schema::hasTable('departement_user')) {
            Schema::rename('departement_user', 'groupe_user');
            Schema::table('groupe_user', function (Blueprint $table) {
                $table->renameColumn('departement_id', 'groupe_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback de la table pivot groupe_user
        if (Schema::hasTable('groupe_user')) {
            Schema::table('groupe_user', function (Blueprint $table) {
                $table->renameColumn('groupe_id', 'departement_id');
            });
            Schema::rename('groupe_user', 'departement_user');
        }

        // Rollback de la table pivot folder_groupe
        if (Schema::hasTable('folder_groupe')) {
            Schema::table('folder_groupe', function (Blueprint $table) {
                $table->renameColumn('groupe_id', 'departement_id');
            });
            Schema::rename('folder_groupe', 'departement_folder');
        }

        // Rollback de la table pivot file_groupe
        if (Schema::hasTable('file_groupe')) {
            Schema::table('file_groupe', function (Blueprint $table) {
                $table->renameColumn('groupe_id', 'departement_id');
            });
            Schema::rename('file_groupe', 'departement_file');
        }

        // Rollback de la table pivot document_groupe
        if (Schema::hasTable('document_groupe')) {
            Schema::table('document_groupe', function (Blueprint $table) {
                $table->renameColumn('groupe_id', 'departement_id');
            });
            Schema::rename('document_groupe', 'departement_document');
        }

        // Rollback de la table principale
        Schema::rename('groupes', 'departements');
    }
};
