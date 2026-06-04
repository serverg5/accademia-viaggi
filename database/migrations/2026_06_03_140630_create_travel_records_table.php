<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_year_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->date('record_date')->nullable()->index();
            $table->string('practice_code')->nullable()->index();
            $table->json('values')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_records');
    }
};
