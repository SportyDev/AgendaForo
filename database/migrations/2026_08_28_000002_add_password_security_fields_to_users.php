<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'debe_cambiar_password')) {
                $table->boolean('debe_cambiar_password')->default(false)->after('password');
            }

            if (! Schema::hasColumn('users', 'password_cambiado_at')) {
                $table->timestamp('password_cambiado_at')->nullable()->after('debe_cambiar_password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'password_cambiado_at')) $columns[] = 'password_cambiado_at';
            if (Schema::hasColumn('users', 'debe_cambiar_password')) $columns[] = 'debe_cambiar_password';
            if ($columns) $table->dropColumn($columns);
        });
    }
};
