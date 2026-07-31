<?php

use Fastchartdev\Package\Models\Event;
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
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Event::class)
                ->constrained()
                ->onDelete('cascade');
            $table->string('aggregation');
            $table->string('period_type');
            $table->timestamps();

            $table->unique(['event_id', 'aggregation', 'period_type'], 'unique_event_aggregation_period');
            // $table->index(['event_id', 'aggregation', 'period_type'], 'index_event_aggregation_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meters');
    }
};
