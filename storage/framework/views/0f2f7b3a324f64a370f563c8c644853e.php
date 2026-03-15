<?php $__env->startSection('title', 'CV Analyzer — Résultat'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $feedback = $analysis->ai_feedback_json; // déjà décodé grâce au cast 'array'
    $sections = [
        'ATS'          => ['🤖 ATS & Mots-clés',   $feedback['ATS']          ?? []],
        'toneAndStyle' => ['✍️  Ton & Style',        $feedback['toneAndStyle'] ?? []],
        'content'      => ['📝 Contenu',             $feedback['content']      ?? []],
        'structure'    => ['🏗️  Structure',           $feedback['structure']    ?? []],
        'skills'       => ['💡 Compétences',          $feedback['skills']       ?? []],
    ];
    $scoreCols = [
        'score_ats'       => 'ATS / Mots-clés',
        'score_tone'      => 'Ton & Style',
        'score_content'   => 'Contenu',
        'score_structure' => 'Structure',
        'score_skills'    => 'Compétences',
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

        <?php $dash = round(314 * $analysis->overall_score / 100); ?>
        <div class="overall-score-circle <?php echo e($analysis->score_color); ?>">
            <svg viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--surface-2)" stroke-width="10"/>
                <circle cx="60" cy="60" r="50" fill="none" stroke="currentColor" stroke-width="10"
                    stroke-dasharray="314" stroke-dashoffset="<?php echo e(314 - $dash); ?>"
                    stroke-linecap="round" transform="rotate(-90 60 60)"/>
            </svg>
            <div class="score-center">
                <span class="score-num"><?php echo e($analysis->overall_score); ?></span>
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
        <?php $__currentLoopData = $scoreCols; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $score = $analysis->$col; ?>
        <div class="score-overview-item <?php echo e(\App\Models\CvAnalysis::colorFor($score)); ?>"
             data-target="#section-<?php echo e($loop->index); ?>">
            <div class="soi-bar-wrap">
                <div class="soi-bar-fill" style="width:<?php echo e($score); ?>%"></div>
            </div>
            <div class="soi-info">
                <span class="soi-label"><?php echo e($label); ?></span>
                <span class="soi-score">
                    <?php echo e($score); ?> —
                    <?php if($score >= 75): ?> Excellent
                    <?php elseif($score >= 50): ?> Correct
                    <?php else: ?> À améliorer
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="feedback-sections">
        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$title, $cat]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(!empty($cat)): ?>
        <?php $score = $cat['score'] ?? 0; ?>
        <div class="feedback-section" id="section-<?php echo e($loop->index); ?>">
            <div class="fs-header">
                <h2><?php echo e($title); ?></h2>
                <span class="fs-score badge-<?php echo e(\App\Models\CvAnalysis::colorFor($score)); ?>">
                    <?php echo e($score); ?>/100
                </span>
            </div>
            <div class="tips-list">
                <?php $__currentLoopData = $cat['tips'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="tip-item tip-<?php echo e($tip['type']); ?>">
                    <div class="tip-icon"><?php echo e($tip['type'] === 'good' ? '✓' : '↑'); ?></div>
                    <div class="tip-body">
                        <strong><?php echo e($tip['tip']); ?></strong>
                        <?php if(!empty($tip['explanation'])): ?>
                            <p><?php echo e($tip['explanation']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="result-footer-actions">
        <a href="<?php echo e(route('analysis.create')); ?>" class="btn btn-primary">Analyser un autre CV</a>
        <a href="<?php echo e(route('dashboard')); ?>"        class="btn btn-outline">Tableau de bord</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cv_analyzer_laravel\resources\views/analysis/show.blade.php ENDPATH**/ ?>