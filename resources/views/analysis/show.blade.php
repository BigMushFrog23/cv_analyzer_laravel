@extends('layouts.app')
@section('title', 'CV Analyzer — Résultat')

@section('content')
@php
    $feedback = $analysis->ai_feedback_json; // déjà décodé grâce au cast 'array'
    $sections = [
        'ATS'          => ['🤖 ATS & Mots-clés',   $feedback['ATS']          ?? []],
        'toneAndStyle' => ['✍️  Ton & Style',        $feedback['toneAndStyle'] ?? []],
        'content'      => ['📝 Contenu',             $feedback['content']      ?? []],
        'structure'    => ['🏗️  Structure',           $feedback['structure']    ?? []],
        'skills'       => ['💡 Compétences',          $feedback['skills']       ?? []],
    ];
    $scoreCols = [
        'score_ats'       => 'ATS / Mots-clés',
        'score_tone'      => 'Ton & Style',
        'score_content'   => 'Contenu',
        'score_structure' => 'Structure',
        'score_skills'    => 'Compétences',
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

        @php $dash = round(314 * $analysis->overall_score / 100); @endphp
        <div class="overall-score-circle {{ $analysis->score_color }}">
            <svg viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--surface-2)" stroke-width="10"/>
                <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="10"
                    stroke-dasharray="314" stroke-dashoffset="{{ 314 - $dash }}"
                    stroke-linecap="round" transform="rotate(-90 60 60)"/>
            </svg>
            <div class="score-center">
                <span class="score-num">{{ $analysis->overall_score }}</span>
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

    {{-- Scores par catégorie --}}
    <div class="scores-overview">
        @foreach ($scoreCols as $col => $label)
        @php $score = $analysis->$col; @endphp
        <div class="score-overview-item {{ \App\Models\CvAnalysis::colorFor($score) }}"
             data-target="#section-{{ $loop->index }}">
            <div class="soi-bar-wrap">
                <div class="soi-bar-fill" style="width:{{ $score }}%"></div>
            </div>
            <div class="soi-info">
                <span class="soi-label">{{ $label }}</span>
                <span class="soi-score">
                    {{ $score }} —
                    @if($score >= 75) Excellent
                    @elseif($score >= 50) Correct
                    @else À améliorer
                    @endif
                </span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Sections détaillées --}}
    <div class="feedback-sections">
        @foreach ($sections as $key => [$title, $cat])
        @if (!empty($cat))
        @php $score = $cat['score'] ?? 0; @endphp
        <div class="feedback-section" id="section-{{ $loop->index }}">
            <div class="fs-header">
                <h2>{{ $title }}</h2>
                <span class="fs-score badge-{{ \App\Models\CvAnalysis::colorFor($score) }}">
                    {{ $score }}/100
                </span>
            </div>
            <div class="tips-list">
                @foreach ($cat['tips'] ?? [] as $tip)
                <div class="tip-item tip-{{ $tip['type'] }}">
                    <div class="tip-icon">{{ $tip['type'] === 'good' ? '✓' : '↑' }}</div>
                    <div class="tip-body">
                        <strong>{{ $tip['tip'] }}</strong>
                        @if (!empty($tip['explanation']))
                            <p>{{ $tip['explanation'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>

    <div class="result-footer-actions">
        <a href="{{ route('analysis.create') }}" class="btn btn-primary">Analyser un autre CV</a>
        <a href="{{ route('dashboard') }}"        class="btn btn-outline">Tableau de bord</a>
    </div>
</div>
@endsection
