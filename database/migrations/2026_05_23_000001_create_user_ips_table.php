<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ip', 45);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ip']);
            $table->unique(['user_id', 'ip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ips');
    }
};
