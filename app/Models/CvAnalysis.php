<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CvAnalysis extends Model
{
    use HasFactory;

    /**
     * Nom de la table en base de données
     */
    protected $table = 'cv_analyses';

    /**
     * Champs remplissables
     */
    protected $fillable = [
        'user_id',
        'job_title',
        'company_name',
        'job_description',
        'years_experience',
        'cv_filename',
        'overall_score',
        'score_ats',
        'score_tone',
        'score_content',
        'score_structure',
        'score_skills',
        'ai_feedback_json',
    ];

    /**
     * Casts : convertir automatiquement les types
     */
    protected $casts = [
        'ai_feedback_json' => 'array',   // JSON ↔ tableau PHP automatiquement
        'overall_score'    => 'integer',
        'score_ats'        => 'integer',
        'score_tone'       => 'integer',
        'score_content'    => 'integer',
        'score_structure'  => 'integer',
        'score_skills'     => 'integer',
        'years_experience' => 'integer',
    ];

    /**
     * Relation inverse : une analyse appartient à un User
     * C'est le côté "N" de la relation 1→N
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor : retourner une couleur CSS selon le score
     * Utilisé directement dans les vues Blade : $analysis->score_color
     */
    public function getScoreColorAttribute(): string
    {
        if ($this->overall_score >= 75) return 'good';
        if ($this->overall_score >= 50) return 'warn';
        return 'bad';
    }

    /**
     * Accessor : label lisible pour le score
     */
    public function getScoreLabelAttribute(): string
    {
        if ($this->overall_score >= 75)
        {
            return 'Excellent';
        }
        if ($this->overall_score >= 50) 
        {
            return 'Correct';
        }
        return 'À améliorer';
    }

    /**
     * Helper statique : couleur selon n'importe quel score
     */
    public static function colorFor(int $score): string
    {
        if ($score >= 75) 
        {
            return 'good';   
        }
        if ($score >= 50)
        {
            return 'warn';
        }
        return 'bad';
    }
}
