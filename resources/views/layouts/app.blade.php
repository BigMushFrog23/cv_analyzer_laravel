<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CV Analyzer')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<nav class="navbar">
    <a href="{{ route('home') }}" class="nav-brand">
        <span class="brand-icon">◈</span>
        <span>CV<strong>Analyzer</strong></span>
    </a>
    <div class="nav-links">
        @auth
            <span class="nav-user">👤 {{ Auth::user()->name }}</span>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('analysis.create') }}" class="btn btn-primary btn-sm">+ Analyser un CV</a>
            {{-- Déconnexion via POST (sécurité CSRF) --}}
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="nav-link btn-reset">Déconnexion</button>
            </form>
        @else
            <a href="{{ route('login') }}"    class="nav-link {{ request()->routeIs('login')    ? 'active' : '' }}">Connexion</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">S'inscrire</a>
        @endauth
    </div>
</nav>

<main class="main-content">
    {{-- Messages flash (succès / erreur) --}}
    @if (session('success'))
        <div class="alert alert-success flash-message">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error flash-message">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>

<footer class="footer">
    <p>© {{ date('Y') }} CVAnalyzer — BTS SIO SLAM — Propulsé par Laravel</p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
