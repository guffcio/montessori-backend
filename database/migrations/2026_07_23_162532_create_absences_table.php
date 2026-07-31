<?php

use App\Models\Child;
use App\Models\User;
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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Child::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class, 'reported_by_user_id')->constrained()->onDelete('cascade');
            $table->boolean('charge_catering');
            $table->date('absent_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
