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
        Schema::create('ai_interaction_feedback', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relationships
            $table->foreignId('ai_interaction_id')->constrained('ai_interactions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');

            // Rating & Feedback
            $table->tinyInteger('rating')->nullable(); // 1-5 stars, or null if just thumbs up/down
            $table->boolean('thumbs_up')->nullable(); // Simple thumbs up/down
            $table->string('feedback_type')->nullable(); // helpful, unhelpful, incorrect, offensive, brilliant, needs_improvement

            // Training Data (Critical for LoRA/Fine-tuning)
            $table->longText('correction_text')->nullable(); // What the AI should have said
            $table->longText('explanation')->nullable(); // Why this feedback is given
            $table->json('tags')->nullable(); // Categorization: ['factual_error', 'tone', 'format', 'completeness']

            // Weighting for Training (importance of this feedback)
            $table->decimal('weight', 3, 2)->default(1.00); // 0.00 - 1.00 (confidence in this feedback)
            $table->boolean('verified_by_admin')->default(false); // Admin can verify high-quality corrections
            $table->foreignId('verified_by')->nullable()->constrained('users'); // Admin who verified

            // Additional Context
            $table->json('metadata')->nullable(); // User expertise level, context, etc.
            $table->text('notes')->nullable(); // Admin notes

            $table->timestamps();

            // Indexes
            $table->index('ai_interaction_id');
            $table->index('user_id');
            $table->index('feedback_type');
            $table->index('verified_by_admin');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_interaction_feedback');
    }
};
