<?php $__env->startSection('title', 'CV Analyzer — Résultat'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $feedback = $analysis->ai_feedback_json; // Cast array via le Model CvAnalysis
    
    // On définit les sections en faisant correspondre les clés JSON aux labels
    // On s'assure de récupérer le score depuis le JSON ou la DB en secours
    $sections = [
        'ATS'          => [
            'label' => '🤖 ATS & Mots-clés',
            'data'  => $feedback['ATS'] ?? [],
            'db_score' => $analysis->score_ats
        ],
        'toneAndStyle' => [
            'label' => '✍️ Ton & Style',
            'data'  => $feedback['toneAndStyle'] ?? [],
            'db_score' => $analysis->score_tone
        ],
        'content'      => [
            'label' => '📝 Contenu',
            'data'  => $feedback['content'] ?? [],
            'db_score' => $analysis->score_content
        ],
        'structure'    => [
            'label' => '🏗️ Structure',
            'data'  => $feedback['structure'] ?? [],
            'db_score' => $analysis->score_structure
        ],
        'skills'       => [
            'label' => '💡 Compétences',
            'data'  => $feedback['skills'] ?? [],
            'db_score' => $analysis->score_skills
        ],
    ];
?>

<div class="result-container">
    <div class="result-nav">
        <a href="<?php echo e(route('dashboard')); ?>" class="back-link">← Tableau de bord</a>
        <div class="result-nav-actions">
            <a href="<?php echo e(route('analysis.edit', $analysis->id)); ?>" class="btn btn-sm btn-ghost">Modifier</a>
            <form method="POST" action="<?php echo e(route('analysis.destroy', $analysis->id)); ?>"
                  style="display:inline"
                  onsubmit="return confirm('Supprimer cette analyse ?')">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
            </form>
        </div>
    </div>

    
    <div class="result-header">
        <div class="result-job-info">
            <h1><?php echo e($analysis->job_title); ?></h1>
            <?php if($analysis->company_name): ?>
                <span class="company-tag lg"><?php echo e($analysis->company_name); ?></span>
            <?php endif; ?>
            <p class="text-muted">
                <?php echo e($analysis->years_experience); ?> an<?php echo e($analysis->years_experience > 1 ? 's' : ''); ?> d'expérience requis
                · Analysé le <?php echo e($analysis->created_at->format('d/m/Y')); ?>

            </p>
        </div>

        <?php
            $overall = $feedback['overallScore'] ?? $analysis->overall_score;
            $dash = round(314 * $overall / 100);
        ?>
        <div class="overall-score-circle <?php echo e(\App\Models\CvAnalysis::colorFor($overall)); ?>">
            <svg viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--surface-2)" stroke-width="10"/>
                <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="10"
                    stroke-dasharray="314" stroke-dashoffset="<?php echo e(314 - $dash); ?>"
                    stroke-linecap="round" transform="rotate(-90 60 60)"/>
            </svg>
            <div class="score-center">
                <span class="score-num"><?php echo e($overall); ?></span>
                <small>/100</small>
            </div>
        </div>
    </div>

    
    <?php if(!empty($feedback['summary'])): ?>
    <div class="ai-summary">
        <span class="ai-label">◈ Résumé IA</span>
        <p><?php echo e($feedback['summary']); ?></p>
    </div>
    <?php endif; ?>

    
    <div class="scores-overview">
        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            // On prend le score du JSON en priorité, sinon celui de la DB
            $currentScore = $info['data']['score'] ?? $info['db_score'] ?? 0;
            $color = \App\Models\CvAnalysis::colorFor($currentScore);
        ?>
        <div class="score-overview-item <?php echo e($color); ?>" style="cursor: pointer;">
            <div class="soi-bar-wrap">
                <div class="soi-bar-fill" style="width:<?php echo e($currentScore); ?>%"></div>
            </div>
            <div class="soi-info">
                <span class="soi-label"><?php echo e($info['label']); ?></span>
                <span class="soi-score">
                    <?php echo e($currentScore); ?> —
                    <?php if($currentScore >= 75): ?> Excellent
                    <?php elseif($currentScore >= 50): ?> Correct
                    <?php else: ?> À améliorer
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="feedback-sections">
        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $cat = $info['data'];
            $score = $cat['score'] ?? $info['db_score'] ?? 0;
        ?>
        <div class="feedback-section">
            <div class="fs-header">
                <h2><?php echo e($info['label']); ?></h2>
                <span class="fs-score badge-<?php echo e(\App\Models\CvAnalysis::colorFor($score)); ?>">
                    <?php echo e($score); ?>/100
                </span>
            </div>
            <div class="tips-list">
                <?php $__empty_1 = true; $__currentLoopData = $cat['tips'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="tip-item tip-<?php echo e($tip['type'] ?? 'improve'); ?>">
                    <div class="tip-icon"><?php echo e(($tip['type'] ?? '') === 'good' ? '✓' : '↑'); ?></div>
                    <div class="tip-body">
                        <strong><?php echo e($tip['tip'] ?? 'Analyse en cours...'); ?></strong>
                        <?php if(!empty($tip['explanation'])): ?>
                            <p><?php echo e($tip['explanation']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="tip-item">
                    <div class="tip-body">
                        <p class="text-muted">Aucun conseil spécifique généré pour cette section.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="result-footer-actions">
        <a href="<?php echo e(route('analysis.create')); ?>" class="btn btn-primary">Analyser un autre CV</a>
        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline">Tableau de bord</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cv_analyzer_laravel\resources\views/analysis/show.blade.php ENDPATH**/ ?>