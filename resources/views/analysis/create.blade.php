@extends('layouts.app')
@section('title', 'CV Analyzer — Analyser mon CV')

@section('content')
<div class="analyze-container">
    <div class="page-header">
        <a href="{{ route('dashboard') }}" class="back-link">← Tableau de bord</a>
        <h1>Analyser votre CV</h1>
        <p class="text-muted">Renseignez l'offre visée, puis uploadez votre CV en PDF</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('analysis.store') }}"
          enctype="multipart/form-data"
          class="analyze-form"
          id="analyzeForm">
        @csrf

        <div class="form-section">
            <h3>🎯 L'offre d'emploi</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="job_title">Titre du poste <span class="required">*</span></label>
                    <input type="text" id="job_title" name="job_title"
                           value="{{ old('job_title') }}"
                           placeholder="ex: Développeur Full-Stack"
                           required>
                </div>
                <div class="form-group">
                    <label for="company_name">Entreprise</label>
                    <input type="text" id="company_name" name="company_name"
                           value="{{ old('company_name') }}"
                           placeholder="ex: Google (optionnel)">
                </div>
            </div>
            <div class="form-group">
                <label for="years_experience">Années d'expérience requises</label>
                <input type="number" id="years_experience" name="years_experience"
                       value="{{ old('years_experience', 0) }}"
                       min="0" max="30">
            </div>
            <div class="form-group">
                <label for="job_description">Description du poste <span class="required">*</span></label>
                <textarea id="job_description" name="job_description" rows="6"
                          placeholder="Collez ici la description complète du poste..."
                          required>{{ old('job_description') }}</textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>📄 Votre CV</h3>
            <div class="upload-zone" id="uploadZone">
                <input type="file" id="cv_file" name="cv_file"
                       accept=".pdf" required class="file-input">
                <div class="upload-icon">⬆</div>
                <p class="upload-text">Glissez votre CV ici ou <strong>cliquez pour sélectionner</strong></p>
                <p class="upload-hint">Format PDF uniquement — 5 Mo max</p>
                <div class="file-preview" id="filePreview" style="display:none">
                    <span class="file-icon">📄</span>
                    <span id="fileName"></span>
                    <button type="button" class="file-remove" onclick="clearFile()" aria-label="Remove file">
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" id="submitBtn">
            <span class="btn-text">✦ Analyser mon CV</span>
            <span class="btn-loading" style="display:none">⏳ Analyse en cours (30-60s)...</span>
        </button>

        {{-- Mini-jeu Snake affiché pendant l'attente de l'analyse --}}
        <div class="snake-wait" id="snakeWait" hidden>
            <p class="snake-wait-title">⏳ Analyse en cours (30–60s)…<br>En attendant, joue à <strong>Snake</strong> !</p>
            <canvas id="snakeCanvas" width="160" height="160" class="snake-canvas"></canvas>
            <div class="snake-score">Score : <span id="snakeScore">0</span></div>
            <p class="snake-controls">⬆ ⬇ ⬅ ➡ Flèches (ou Z Q S D) pour diriger · Espace pour rejouer</p>
        </div>
    </form>
</div>

<style>
    .snake-wait {
        margin-top: 24px;
        text-align: center;
        padding: 22px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.03);
    }
    .snake-wait-title { margin-bottom: 14px; font-weight: 600; line-height: 1.5; }
    .snake-canvas {
        image-rendering: pixelated;
        width: 240px;
        height: 240px;
        border: 3px solid rgba(255, 255, 255, 0.15);
        border-radius: 6px;
        background: #0e1117;
        touch-action: none;
    }
    .snake-score { margin-top: 12px; font-family: monospace; font-size: 1.1rem; }
    .snake-controls { margin-top: 8px; font-size: 0.82rem; opacity: 0.7; }
</style>

<script src="{{ asset('js/snake.js') }}"></script>
@endsection
