<?php

use Fastchartdev\Package\Models\Meter;
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
        Schema::connection(config('fastchart.database.main.connection', 'sqlite'))->create('meter_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Meter::class)
                ->constrained()
                ->onDelete('cascade');
            $table->string('scope_value');
            $table->decimal('value', 10, 2);
            $table->bigInteger('count')->nullable();
            $table->integer('at');
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->timestamps();

            $table->unique(['meter_id', 'scope_value', 'at'], 'unique_meter_scope_at');
            // $table->index(['meter_id', 'scope_value', 'at'], 'index_meter_scope_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('fastchart.database.main.connection', 'sqlite'))->dropIfExists('meter_summaries');
    }
};
