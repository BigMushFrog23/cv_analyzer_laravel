<?php $__env->startSection('title', 'CV Analyzer — Accueil'); ?>

<?php $__env->startSection('content'); ?>
<section class="hero">
    <div class="hero-content">
        <h1>Votre CV, <span class="gradient-text">analysé</span><br>en quelques secondes</h1>
        <p class="hero-sub">
            Téléchargez votre CV, renseignez l'offre d'emploi ciblée.<br>
            Notre IA vous donne un score détaillé et des conseils actionnables.
        </p>
        <div class="hero-actions">
            <a href="<?php echo e(route('register')); ?>" class="btn btn-primary btn-lg">Commencer gratuitement</a>
            <a href="<?php echo e(route('login')); ?>"    class="btn btn-outline btn-lg">Se connecter</a>
        </div>
    </div>
    <div class="hero-visual">
        <div class="score-card-demo">
            <div class="score-ring">
                <svg viewBox="0 0 100 100" class="ring-svg">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="var(--surface-2)" stroke-width="8"/>
                    <circle cx="50" cy="50" r="40" fill="none" stroke="url(#grad)" stroke-width="8"
                        stroke-dasharray="213" stroke-dashoffset="32" stroke-linecap="round"
                        transform="rotate(-90 50 50)"/>
                    <defs>
                        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="var(--accent)"/>
                            <stop offset="100%" stop-color="var(--accent-2)"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="ring-label">
                    <span class="ring-num">85</span><small>/100</small>
                </div>
            </div>
            <div class="demo-bars">
                <?php $__currentLoopData = [['ATS',90,'good'],['Style',82,'good'],['Contenu',78,'warn'],['Structure',88,'good'],['Compétences',75,'warn']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label,$score,$cls]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="demo-bar-row">
                    <span><?php echo e($label); ?></span>
                    <div class="demo-bar-track">
                        <div class="demo-bar-fill <?php echo e($cls); ?>" style="width:<?php echo e($score); ?>%"></div>
                    </div>
                    <span class="demo-bar-val"><?php echo e($score); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</section>

<section class="features">
    <div class="feature-card">
        <div class="feat-icon">◈</div>
        <h3>Score ATS</h3>
        <p>Vérifiez si votre CV passe les filtres automatiques des recruteurs</p>
    </div>
    <div class="feature-card">
        <div class="feat-icon">◎</div>
        <h3>Feedback détaillé</h3>
        <p>Ton, contenu, structure, compétences — chaque aspect analysé</p>
    </div>
    <div class="feature-card">
        <div class="feat-icon">◉</div>
        <h3>Historique complet</h3>
        <p>Retrouvez toutes vos analyses dans votre tableau de bord personnel</p>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cv_analyzer_laravel\resources\views/home.blade.php ENDPATH**/ ?>
