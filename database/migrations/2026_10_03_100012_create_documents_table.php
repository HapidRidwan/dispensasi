<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensation_id')->constrained()->cascadeOnDelete();
            $table->string('document_number')->unique();
            $table->string('document_type')->default('DISPENSASI');
            $table->string('file_path')->nullable();
            $table->string('document_status')->default('GENERATED');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('stamped_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
