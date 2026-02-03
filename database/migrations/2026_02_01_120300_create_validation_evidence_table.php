<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('validation_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('validation_records');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('path');
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_evidence');
    }
};
