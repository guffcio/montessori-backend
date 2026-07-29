<?php

use App\Models\Allergen;
use App\Models\Child;
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
        Schema::create('allergen_child', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Child::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Allergen::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allergen_child');
    }
};
