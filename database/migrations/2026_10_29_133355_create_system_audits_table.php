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
        Schema::create('system_audits', function (Blueprint $table) {
            $table->id();
            $table->string('triggered_by')->default('cli'); // cli, web, schedule
            $table->string('status'); // clean, warning, critical
            $table->unsignedInteger('total_checks');
            $table->unsignedInteger('checks_passed');
            $table->unsignedInteger('checks_failed');
            $table->unsignedInteger('checks_warning');
            $table->decimal('duration_ms', 8, 2);
            $table->text('summary')->nullable();
            $table->json('report_json');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_audits');
    }
};
