@extends('layouts.app')
@section('title', 'CV Analyzer — Mon compte')

@section('content')
<style>
    .danger-zone { border: 1px solid rgba(239, 68, 68, 0.4); }
    .danger-zone h3 { color: #ef4444; }
    .danger-zone .text-muted { margin-bottom: 16px; }
</style>

<div class="analyze-container">
    <div class="page-header">
        <a href="{{ route('dashboard') }}" class="back-link">← Tableau de bord</a>
        <h1>Mon compte</h1>
        <p class="text-muted">Gérez les informations de votre compte</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="form-section">
        <h3>👤 Informations</h3>
        <div class="form-group">
            <label>Nom</label>
            <input type="text" value="{{ Auth::user()->name }}" disabled>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="{{ Auth::user()->email }}" disabled>
        </div>
    </div>

    <div class="form-section danger-zone">
        <h3>⚠️ Zone de danger</h3>
        <p class="text-muted">
            La suppression de votre compte est <strong>définitive</strong>.
            Toutes vos analyses et vos CV uploadés seront effacés. Cette action est irréversible.
        </p>
        <form method="POST" action="{{ route('account.destroy') }}"
              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement votre compte ?')">
            @csrf
            @method('DELETE')
            <div class="form-group">
                <label for="password">Confirmez avec votre mot de passe <span class="required">*</span></label>
                <input type="password" id="password" name="password"
                       placeholder="Votre mot de passe" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-danger">Supprimer définitivement mon compte</button>
        </form>
    </div>
</div>
@endsection
