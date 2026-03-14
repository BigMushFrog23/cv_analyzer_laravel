@extends('layouts.app')
@section('title', 'CV Analyzer — Tableau de bord')

@section('content')
<div class="dashboard">

    <div class="dash-header">
        <div>
            <h1>Bonjour, {{ explode(' ', Auth::user()->name)[0] }} 👋</h1>
            <p class="text-muted">Voici un aperçu de vos analyses CV</p>
        </div>
        <a href="{{ route('analysis.create') }}" class="btn btn-primary">+ Analyser un nouveau CV</a>
    </div>

    {{-- Statistiques --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num">{{ $stats['total'] }}</div>
            <div class="stat-label">CV analysés</div>
        </div>
        <div class="stat-card">
            <div class="stat-num {{ \App\Models\CvAnalysis::colorFor((int)$stats['avg_score']) }}">
                {{ $stats['avg_score'] ?? '—' }}
            </div>
            <div class="stat-label">Score moyen</div>
        </div>
        <div class="stat-card">
            <div class="stat-num good">{{ $stats['best_score'] ?? '—' }}</div>
            <div class="stat-label">Meilleur score</div>
        </div>
        <div class="stat-card">
            <div class="stat-num bad">{{ $stats['worst_score'] ?? '—' }}</div>
            <div class="stat-label">Score le plus bas</div>
        </div>
    </div>

    <div class="analyses-header">
        <h2>Mes analyses</h2>
    </div>

    @if ($analyses->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">📄</div>
            <h3>Aucune analyse pour l'instant</h3>
            <p>Téléchargez votre premier CV pour commencer</p>
            <a href="{{ route('analysis.create') }}" class="btn btn-primary">Analyser mon CV</a>
        </div>
    @else
        <div class="cards-grid">
            @foreach ($analyses as $analysis)
            <div class="analysis-card">
                <div class="card-top">
                    <div class="card-job">
                        <strong>{{ $analysis->job_title }}</strong>
                        @if ($analysis->company_name)
                            <span class="company-tag">{{ $analysis->company_name }}</span>
                        @endif
                    </div>
                    <div class="card-score {{ $analysis->score_color }}">
                        {{ $analysis->overall_score }}
                    </div>
                </div>

                <div class="mini-scores">
                    @foreach(['score_ats'=>'ATS','score_tone'=>'Style','score_content'=>'Contenu','score_structure'=>'Structure','score_skills'=>'Skills'] as $col => $label)
                    <div class="mini-score-item">
                        <div class="mini-bar-track">
                            <div class="mini-bar-fill {{ \App\Models\CvAnalysis::colorFor($analysis->$col) }}"
                                 style="width:{{ $analysis->$col }}%"></div>
                        </div>
                        <span>{{ $label }} {{ $analysis->$col }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="card-date">
                    {{ $analysis->created_at->format('d/m/Y à H:i') }}
                </div>

                <div class="card-actions">
                    <a href="{{ route('analysis.show', $analysis->id) }}" class="btn btn-sm btn-outline">Voir détails</a>
                    <a href="{{ route('analysis.edit', $analysis->id) }}" class="btn btn-sm btn-ghost">Modifier</a>
                    {{-- DELETE via formulaire POST avec méthode spoofing Laravel --}}
                    <form method="POST" action="{{ route('analysis.destroy', $analysis->id) }}"
                          style="display:inline"
                          onsubmit="return confirm('Supprimer cette analyse ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
