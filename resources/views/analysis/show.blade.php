@extends('layouts.app')
@section('title', 'CV Analyzer — Résultat')

@section('content')
@php
    $feedback = $analysis->ai_feedback_json ?? [];
    
    $getCategoryData = function($key) use ($feedback, $analysis) {
        // Liste de synonymes par catégorie pour parer aux variations de l'IA
        $mapping = [
            'ATS'          => ['ATS', 'ats', 'keywords', 'mots_cles', 'Keywords'],
            'toneAndStyle' => ['toneAndStyle', 'style', 'ton', 'tonalite', 'tonAndStyle', 'presentation'],
            'content'      => ['content', 'contenu', 'Contenu', 'details'],
            'structure'    => ['structure', 'Structure', 'mise_en_page', 'layout'],
            'skills'       => ['skills', 'competences', 'Skills', 'Competences', 'capacites'],
        ];

        $variants = $mapping[$key] ?? [$key];

        // 1. On cherche dans le JSON parmi tous les synonymes
        foreach ($variants as $v) {
            if (isset($feedback[$v])) {
                if (is_array($feedback[$v])) {
                    return [
                        'score' => $feedback[$v]['score'] ?? 0,
                        'tips'  => $feedback[$v]['tips'] ?? []
                    ];
                }
                if (is_numeric($feedback[$v])) {
                    return ['score' => (int)$feedback[$v], 'tips' => []];
                }
            }
        }

        // 2. Si rien trouvé dans le JSON, on force la valeur de la DB
        $db_map = [
            'ATS'          => $analysis->score_ats,
            'toneAndStyle' => $analysis->score_tone,
            'content'      => $analysis->score_content,
            'structure'    => $analysis->score_structure,
            'skills'       => $analysis->score_skills,
        ];
        
        return ['score' => $db_map[$key] ?? 0, 'tips' => []];
    };

    // Construction de l'objet final utilisé par la vue
    $sections = [
        'ATS'          => array_merge(['label' => '🤖 ATS & Mots-clés'], $getCategoryData('ATS')),
        'toneAndStyle' => array_merge(['label' => '✍️ Ton & Style'],      $getCategoryData('toneAndStyle')),
        'content'      => array_merge(['label' => '📝 Contenu'],           $getCategoryData('content')),
        'structure'    => array_merge(['label' => '🏗️ Structure'],         $getCategoryData('structure')),
        'skills'       => array_merge(['label' => '💡 Compétences'],       $getCategoryData('skills')),
    ];

    $overall = $feedback['overallScore'] ?? $analysis->overall_score;
    $dash = round(314 * $overall / 100);
@endphp

<div class="result-container">
    <div class="result-nav">
        <a href="{{ route('dashboard') }}" class="back-link">← Tableau de bord</a>
        <div class="result-nav-actions">
            <a href="{{ route('analysis.edit', $analysis->id) }}" class="btn btn-sm btn-ghost">Modifier</a>
            <form method="POST" action="{{ route('analysis.destroy', $analysis->id) }}"
                  style="display:inline"
                  onsubmit="return confirm('Supprimer cette analyse ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
            </form>
        </div>
    </div>

    {{-- En-tête résultat --}}
    <div class="result-header">
        <div class="result-job-info">
            <h1>{{ $analysis->job_title }}</h1>
            @if ($analysis->company_name)
                <span class="company-tag lg">{{ $analysis->company_name }}</span>
            @endif
            <p class="text-muted">
                {{ $analysis->years_experience }} an{{ $analysis->years_experience > 1 ? 's' : '' }} d'expérience requis
                · Analysé le {{ $analysis->created_at->format('d/m/Y') }}
            </p>
        </div>

        <div class="overall-score-circle {{ \App\Models\CvAnalysis::colorFor($overall) }}">
            <svg viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--surface-2)" stroke-width="10"/>
                <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="10"
                    stroke-dasharray="314" stroke-dashoffset="{{ 314 - $dash }}"
                    stroke-linecap="round" transform="rotate(-90 60 60)"/>
            </svg>
            <div class="score-center">
                <span class="score-num">{{ $overall }}</span>
                <small>/100</small>
            </div>
        </div>
    </div>

    {{-- Résumé IA --}}
    @if (!empty($feedback['summary']))
    <div class="ai-summary">
        <span class="ai-label">◈ Résumé IA</span>
        <p>{{ $feedback['summary'] }}</p>
    </div>
    @endif

    {{-- Scores par catégorie (Barres horizontales) --}}
    <div class="scores-overview">
        @foreach ($sections as $key => $section)
        @php $color = \App\Models\CvAnalysis::colorFor($section['score']); @endphp
        <div class="score-overview-item {{ $color }}">
            <div class="soi-bar-wrap">
                <div class="soi-bar-fill" style="width:{{ $section['score'] }}%"></div>
            </div>
            <div class="soi-info">
                <span class="soi-label">{{ $section['label'] }}</span>
                <span class="soi-score">
                    {{ $section['score'] }} — 
                    @if($section['score'] >= 75) Excellent
                    @elseif($section['score'] >= 50) Correct
                    @else À améliorer
                    @endif
                </span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Sections détaillées (Tips) --}}
    <div class="feedback-sections">
        @foreach ($sections as $key => $section)
        <div class="feedback-section">
            <div class="fs-header">
                <h2>{{ $section['label'] }}</h2>
                <span class="fs-score badge-{{ \App\Models\CvAnalysis::colorFor($section['score']) }}">
                    {{ $section['score'] }}/100
                </span>
            </div>
            <div class="tips-list">
                @forelse ($section['tips'] as $tip)
                <div class="tip-item tip-{{ $tip['type'] ?? 'improve' }}">
                    <div class="tip-icon">{{ ($tip['type'] ?? '') === 'good' ? '✓' : '↑' }}</div>
                    <div class="tip-body">
                        <strong>{{ $tip['tip'] ?? 'Conseil IA' }}</strong>
                        @if (!empty($tip['explanation']))
                            <p>{{ $tip['explanation'] }}</p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="tip-item">
                    <div class="tip-body">
                        <p class="text-muted">Aucun conseil spécifique généré pour cette section.</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    <div class="result-footer-actions">
        <a href="{{ route('analysis.create') }}" class="btn btn-primary">Analyser un autre CV</a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline">Tableau de bord</a>
    </div>
</div>
@endsection
