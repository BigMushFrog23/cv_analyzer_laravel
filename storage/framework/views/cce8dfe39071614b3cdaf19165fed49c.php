<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'CV Analyzer'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
</head>
<body>

<nav class="navbar">
    <a href="<?php echo e(route('home')); ?>" class="nav-brand">
        <span class="brand-icon">◈</span>
        <span>CV<strong>Analyzer</strong></span>
    </a>
    <div class="nav-links">
        <?php if(auth()->guard()->check()): ?>
            <span class="nav-user">👤 <?php echo e(Auth::user()->name); ?></span>
            <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                Dashboard
            </a>
            <a href="<?php echo e(route('analysis.create')); ?>" class="btn btn-primary btn-sm">+ Analyser un CV</a>
            
            <form method="POST" action="<?php echo e(route('logout')); ?>" style="display:inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="nav-link btn-reset">Déconnexion</button>
            </form>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>"    class="nav-link <?php echo e(request()->routeIs('login')    ? 'active' : ''); ?>">Connexion</a>
            <a href="<?php echo e(route('register')); ?>" class="btn btn-primary btn-sm">S'inscrire</a>
        <?php endif; ?>
    </div>
</nav>

<main class="main-content">
    
    <?php if(session('success')): ?>
        <div class="alert alert-success flash-message"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-error flash-message"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>
</main>

<footer class="footer">
    <p>© <?php echo e(date('Y')); ?> CVAnalyzer — BTS SIO SLAM — Propulsé par Laravel</p>
</footer>

<script src="<?php echo e(asset('js/app.js')); ?>"></script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\cv_analyzer_laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>
