<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_analyses', function (Blueprint $table) {
            $table->id();
            // Clé étrangère vers users — relation 1→N
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('job_title', 200);
            $table->string('company_name', 200)->nullable();
            $table->text('job_description');
            $table->unsignedTinyInteger('years_experience')->default(0);
            $table->string('cv_filename');
            // Scores sur 100
            $table->unsignedTinyInteger('overall_score')->default(0);
            $table->unsignedTinyInteger('score_ats')->default(0);
            $table->unsignedTinyInteger('score_tone')->default(0);
            $table->unsignedTinyInteger('score_content')->default(0);
            $table->unsignedTinyInteger('score_structure')->default(0);
            $table->unsignedTinyInteger('score_skills')->default(0);
            // Feedback complet en JSON
            $table->longText('ai_feedback_json')->nullable();
            $table->timestamps();

            // Index pour accélérer les requêtes par user
            $table->index('user_id');
            $table->index('overall_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_analyses');
    }
};
