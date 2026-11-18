<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Las columnas creadas con $table->timestamps() no tienen DEFAULT ni ON UPDATE en MySQL,
 * por lo que solo se actualizan cuando el cambio pasa por Eloquent. Cualquier edición directa
 * en la base (SQL, phpMyAdmin, importaciones) dejaba updated_at sin modificar o en NULL,
 * rompiendo el endpoint incremental de árboles (`fecha`, `Last-Modified`).
 *
 * Esta migración hace que MySQL mantenga ambas columnas por sí mismo.
 */
return new class extends Migration
{
    private array $tables = [
        'fuentes',
        'usuarios',
        'familias',
        'tipos',
        'especies',
        'arboles',
        'registros',
        'aportes',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            // Completar valores faltantes antes de volver las columnas NOT NULL
            DB::statement("UPDATE `$table` SET `created_at` = COALESCE(`created_at`, `updated_at`, CURRENT_TIMESTAMP) WHERE `created_at` IS NULL");
            DB::statement("UPDATE `$table` SET `updated_at` = COALESCE(`created_at`, CURRENT_TIMESTAMP) WHERE `updated_at` IS NULL");

            DB::statement("ALTER TABLE `$table`
                MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `$table`
                MODIFY `created_at` TIMESTAMP NULL DEFAULT NULL,
                MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL");
        }
    }
};
