<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'estado')) {
                $table->string('estado', 20)->default('activo')->index()->after('role');
            }

            if (! Schema::hasColumn('users', 'ultimo_acceso_at')) {
                $table->timestamp('ultimo_acceso_at')->nullable();
            }

            if (! Schema::hasColumn('users', 'ultima_ip')) {
                $table->string('ultima_ip', 45)->nullable();
            }

            if (! Schema::hasColumn('users', 'debe_cambiar_password')) {
                $table->boolean('debe_cambiar_password')->default(false);
            }

            if (! Schema::hasColumn('users', 'password_cambiado_at')) {
                $table->timestamp('password_cambiado_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            foreach ([
                'telefono',
                'estado',
                'ultimo_acceso_at',
                'ultima_ip',
                'debe_cambiar_password',
                'password_cambiado_at',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
