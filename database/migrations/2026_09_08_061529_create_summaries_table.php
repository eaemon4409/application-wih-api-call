<?php

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
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('url');
            $table->string('domain')->index();
            $table->string('title')->nullable();
            $table->string('provider'); // 'groq' or 'openrouter'
            $table->string('model');
            $table->string('format')->default('tldr'); // 'tldr', 'comprehensive', 'bullets', 'eli5'
            $table->string('length')->default('medium'); // 'short', 'medium', 'long'
            $table->string('language')->default('English');
            $table->unsignedInteger('original_word_count')->default(0);
            $table->unsignedInteger('summary_word_count')->default(0);
            $table->decimal('reading_time_saved_minutes', 5, 1)->default(0);
            $table->longText('summary_markdown');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};
