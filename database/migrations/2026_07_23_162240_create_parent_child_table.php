<?php

use App\Models\Child;
use App\Models\ParentUser;
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
        Schema::create('parent_child', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Child::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(ParentUser::class, 'parent_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_child');
    }
};
