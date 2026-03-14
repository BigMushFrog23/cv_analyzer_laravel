@extends('layouts.app')
@section('title', 'CV Analyzer — Connexion')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="{{ route('home') }}" class="back-link">← Retour</a>
            <h2>Connexion</h2>
            <p>Accédez à votre tableau de bord</p>
        </div>

        {{-- Afficher les erreurs de validation --}}
        @if ($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- @csrf génère automatiquement un token caché pour sécuriser le formulaire --}}
        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="vous@exemple.fr"
                       class="{{ $errors->has('email') ? 'input-error' : '' }}"
                       required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       placeholder="Votre mot de passe"
                       required>
            </div>

            <div class="form-group form-check">
                <label class="check-label">
                    <input type="checkbox" name="remember" id="remember">
                    Se souvenir de moi
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
        </form>

        <p class="auth-switch">
            Pas encore de compte ?
            <a href="{{ route('register') }}">S'inscrire</a>
        </p>
    </div>
</div>
@endsection
