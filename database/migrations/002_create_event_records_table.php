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
        Schema::create('event_records', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Fastchartdev\Package\Models\Event::class)
                ->constrained()
                ->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->timestamp('timestamp');
            $table->string('scope_value');
            $table->string('status');
            $table->timestamp('started_at');
            $table->timestamp('completed_at');
            $table->timestamp('failed_at');
            $table->string('failure_reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_records');
    }
};
