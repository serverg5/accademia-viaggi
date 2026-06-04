<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_select_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_column_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['travel_column_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_select_options');
    }
};
