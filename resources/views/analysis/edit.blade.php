@extends('layouts.app')
@section('title', 'CV Analyzer — Modifier')

@section('content')
<div class="analyze-container">
    <div class="page-header">
        <a href="{{ route('analysis.show', $analysis->id) }}" class="back-link">← Retour au résultat</a>
        <h1>Modifier l'analyse</h1>
        <p class="text-muted">Modifiez les informations du poste (le score IA ne sera pas recalculé)</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    {{-- PUT via méthode spoofing : les navigateurs ne supportent que GET/POST --}}
    <form method="POST" action="{{ route('analysis.update', $analysis->id) }}" class="analyze-form">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h3>🎯 Informations du poste</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="job_title">Titre du poste <span class="required">*</span></label>
                    <input type="text" id="job_title" name="job_title"
                           value="{{ old('job_title', $analysis->job_title) }}" required>
                </div>
                <div class="form-group">
                    <label for="company_name">Entreprise</label>
                    <input type="text" id="company_name" name="company_name"
                           value="{{ old('company_name', $analysis->company_name) }}">
                </div>
            </div>
            <div class="form-group">
                <label for="years_experience">Années d'expérience requises</label>
                <input type="number" id="years_experience" name="years_experience"
                       value="{{ old('years_experience', $analysis->years_experience) }}"
                       min="0" max="30">
            </div>
            <div class="form-group">
                <label for="job_description">Description du poste <span class="required">*</span></label>
                <textarea id="job_description" name="job_description" rows="6" required>{{ old('job_description', $analysis->job_description) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="{{ route('analysis.show', $analysis->id) }}" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>
@endsection
