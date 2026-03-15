@extends('layouts.app')
@section('title', 'CV Analyzer — Résultat')

@section('content')
@php
    $feedback = $analysis->ai_feedback_json; // Cast array via le Model CvAnalysis
    
    // On définit les sections en faisant correspondre les clés JSON aux labels
    // On s'assure de récupérer le score depuis le JSON ou la DB en secours
    $sections = [
        'ATS'          => [
            'label' => '🤖 ATS & Mots-clés',
            'data'  => $feedback['ATS'] ?? [],
            'db_score' => $analysis->score_ats
        ],
        'toneAndStyle' => [
            'label' => '✍️ Ton & Style',
            'data'  => $feedback['toneAndStyle'] ?? [],
            'db_score' => $analysis->score_tone
        ],
        'content'      => [
            'label' => '📝 Contenu',
            'data'  => $feedback['content'] ?? [],
            'db_score' => $analysis->score_content
        ],
        'structure'    => [
            'label' => '🏗️ Structure',
            'data'  => $feedback['structure'] ?? [],
            'db_score' => $analysis->score_structure
        ],
        'skills'       => [
            'label' => '💡 Compétences',
            'data'  => $feedback['skills'] ?? [],
            'db_score' => $analysis->score_skills
        ],
    ];
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

        @php 
            $overall = $feedback['overallScore'] ?? $analysis->overall_score;
            $dash = round(314 * $overall / 100); 
        @endphp
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
        @foreach ($sections as $key => $info)
        @php 
            // On prend le score du JSON en priorité, sinon celui de la DB
            $currentScore = $info['data']['score'] ?? $info['db_score'] ?? 0;
            $color = \App\Models\CvAnalysis::colorFor($currentScore);
        @endphp
        <div class="score-overview-item {{ $color }}" style="cursor: pointer;">
            <div class="soi-bar-wrap">
                <div class="soi-bar-fill" style="width:{{ $currentScore }}%"></div>
            </div>
            <div class="soi-info">
                <span class="soi-label">{{ $info['label'] }}</span>
                <span class="soi-score">
                    {{ $currentScore }} —
                    @if($currentScore >= 75) Excellent
                    @elseif($currentScore >= 50) Correct
                    @else À améliorer
                    @endif
                </span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Sections détaillées (Tips) --}}
    <div class="feedback-sections">
        @foreach ($sections as $key => $info)
        @php 
            $cat = $info['data'];
            $score = $cat['score'] ?? $info['db_score'] ?? 0;
        @endphp
        <div class="feedback-section">
            <div class="fs-header">
                <h2>{{ $info['label'] }}</h2>
                <span class="fs-score badge-{{ \App\Models\CvAnalysis::colorFor($score) }}">
                    {{ $score }}/100
                </span>
            </div>
            <div class="tips-list">
                @forelse ($cat['tips'] ?? [] as $tip)
                <div class="tip-item tip-{{ $tip['type'] ?? 'improve' }}">
                    <div class="tip-icon">{{ ($tip['type'] ?? '') === 'good' ? '✓' : '↑' }}</div>
                    <div class="tip-body">
                        <strong>{{ $tip['tip'] ?? 'Analyse en cours...' }}</strong>
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
