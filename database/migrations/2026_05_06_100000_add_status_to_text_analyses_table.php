<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('text_analyses', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending')
                ->after('user_id');

            $table->float('ai_score')->nullable()->change();
            $table->string('classification')->nullable()->change();
            $table->json('explanation')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('text_analyses', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->float('ai_score')->nullable(false)->change();
            $table->string('classification')->nullable(false)->change();
            $table->json('explanation')->nullable(false)->change();
        });
    }
};
