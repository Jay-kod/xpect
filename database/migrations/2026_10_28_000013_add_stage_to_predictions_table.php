<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->string('stage')->default('pre_match')->after('is_backtest');
            $table->dropUnique(['match_id', 'model_version', 'is_backtest']);
            $table->unique(['match_id', 'model_version', 'is_backtest', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropUnique(['match_id', 'model_version', 'is_backtest', 'stage']);
            $table->unique(['match_id', 'model_version', 'is_backtest']);
            $table->dropColumn('stage');
        });
    }
};
