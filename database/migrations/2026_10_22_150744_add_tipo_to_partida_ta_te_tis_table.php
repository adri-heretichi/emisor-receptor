
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partida_ta_te_tis', function (Blueprint $table) {
            $table->string('tipo', 10)
                ->default('privada')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('partida_ta_te_tis', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
