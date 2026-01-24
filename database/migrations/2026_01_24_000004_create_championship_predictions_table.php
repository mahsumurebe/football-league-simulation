<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('championship_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->integer('week');
            $table->decimal('probability', 5, 2); // 0.00 to 100.00
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('championship_predictions');
    }
};
