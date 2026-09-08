<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('priority')->default('normal')->index();
            $table->string('status')->default('new')->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
