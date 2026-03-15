<?php $__env->startSection('title', 'CV Analyzer — Résultat'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $feedback = $analysis->ai_feedback_json ?? [];
    
    $getCategoryData = function($key) use ($feedback, $analysis) {
        // Liste de synonymes par catégorie pour parer aux variations de l'IA
        $mapping = [
            'ATS'          => ['ATS', 'ats', 'keywords', 'mots_cles', 'Keywords'],
            'toneAndStyle' => ['toneAndStyle', 'style', 'ton', 'tonalite', 'tonAndStyle', 'presentation'],
            'content'      => ['content', 'contenu', 'Contenu', 'details'],
            'structure'    => ['structure', 'Structure', 'mise_en_page', 'layout'],
            'skills'       => ['skills', 'competences', 'Skills', 'Competences', 'capacites'],
        ];

        $variants = $mapping[$key] ?? [$key];

        // 1. On cherche dans le JSON parmi tous les synonymes
        foreach ($variants as $v) {
            if (isset($feedback[$v])) {
                if (is_array($feedback[$v])) {
                    return [
                        'score' => $feedback[$v]['score'] ?? 0,
                        'tips'  => $feedback[$v]['tips'] ?? []
                    ];
                }
                if (is_numeric($feedback[$v])) {
                    return ['score' => (int)$feedback[$v], 'tips' => []];
                }
            }
        }

        // 2. Si rien trouvé dans le JSON, on force la valeur de la DB
        $db_map = [
            'ATS'          => $analysis->score_ats,
            'toneAndStyle' => $analysis->score_tone,
            'content'      => $analysis->score_content,
            'structure'    => $analysis->score_structure,
            'skills'       => $analysis->score_skills,
        ];
        
        return ['score' => $db_map[$key] ?? 0, 'tips' => []];
    };

    // Construction de l'objet final utilisé par la vue
    $sections = [
        'ATS'          => array_merge(['label' => '🤖 ATS & Mots-clés'], $getCategoryData('ATS')),
        'toneAndStyle' => array_merge(['label' => '✍️ Ton & Style'],      $getCategoryData('toneAndStyle')),
        'content'      => array_merge(['label' => '📝 Contenu'],           $getCategoryData('content')),
        'structure'    => array_merge(['label' => '🏗️ Structure'],         $getCategoryData('structure')),
        'skills'       => array_merge(['label' => '💡 Compétences'],       $getCategoryData('skills')),
    ];

    $overall = $feedback['overallScore'] ?? $analysis->overall_score;
    $dash = round(314 * $overall / 100);
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
        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $color = \App\Models\CvAnalysis::colorFor($section['score']); ?>
        <div class="score-overview-item <?php echo e($color); ?>">
            <div class="soi-bar-wrap">
                <div class="soi-bar-fill" style="width:<?php echo e($section['score']); ?>%"></div>
            </div>
            <div class="soi-info">
                <span class="soi-label"><?php echo e($section['label']); ?></span>
                <span class="soi-score">
                    <?php echo e($section['score']); ?> — 
                    <?php if($section['score'] >= 75): ?> Excellent
                    <?php elseif($section['score'] >= 50): ?> Correct
                    <?php else: ?> À améliorer
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="feedback-sections">
        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="feedback-section">
            <div class="fs-header">
                <h2><?php echo e($section['label']); ?></h2>
                <span class="fs-score badge-<?php echo e(\App\Models\CvAnalysis::colorFor($section['score'])); ?>">
                    <?php echo e($section['score']); ?>/100
                </span>
            </div>
            <div class="tips-list">
                <?php $__empty_1 = true; $__currentLoopData = $section['tips']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="tip-item tip-<?php echo e($tip['type'] ?? 'improve'); ?>">
                    <div class="tip-icon"><?php echo e(($tip['type'] ?? '') === 'good' ? '✓' : '↑'); ?></div>
                    <div class="tip-body">
                        <strong><?php echo e($tip['tip'] ?? 'Conseil IA'); ?></strong>
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