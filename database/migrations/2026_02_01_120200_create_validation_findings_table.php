<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('validation_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('validation_records');
            $table->string('type');
            $table->string('severity');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_findings');
    }
};
