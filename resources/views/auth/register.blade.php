@extends('layouts.app')
@section('title', 'CV Analyzer — Inscription')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="{{ route('home') }}" class="back-link">← Retour</a>
            <h2>Créer un compte</h2>
            <p>Commencez à analyser vos CVs gratuitement</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0;padding-left:1.2rem">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name"
                       value="{{ old('name') }}"
                       placeholder="Jean Dupont"
                       class="{{ $errors->has('name') ? 'input-error' : '' }}"
                       required>
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="vous@exemple.fr"
                       class="{{ $errors->has('email') ? 'input-error' : '' }}"
                       required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe <small>(8 caractères min.)</small></label>
                <input type="password" id="password" name="password"
                       placeholder="Créez un mot de passe fort"
                       minlength="8" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       placeholder="Répétez le mot de passe"
                       required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Créer mon compte</button>
        </form>

        <p class="auth-switch">
            Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
        </p>
    </div>
</div>
@endsection
