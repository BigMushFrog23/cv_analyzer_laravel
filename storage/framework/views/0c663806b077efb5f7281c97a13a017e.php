<?php $__env->startSection('title', 'CV Analyzer — Tableau de bord'); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard">

    <div class="dash-header">
        <div>
            <h1>Bonjour, <?php echo e(explode(' ', Auth::user()->name)[0]); ?> 👋</h1>
            <p class="text-muted">Voici un aperçu de vos analyses CV</p>
        </div>
        <a href="<?php echo e(route('analysis.create')); ?>" class="btn btn-primary">+ Analyser un nouveau CV</a>
    </div>

    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num"><?php echo e($stats['total']); ?></div>
            <div class="stat-label">CV analysés</div>
        </div>
        <div class="stat-card">
            <div class="stat-num <?php echo e(\App\Models\CvAnalysis::colorFor((int)$stats['avg_score'])); ?>">
                <?php echo e($stats['avg_score'] ?? '—'); ?>

            </div>
            <div class="stat-label">Score moyen</div>
        </div>
        <div class="stat-card">
            <div class="stat-num good"><?php echo e($stats['best_score'] ?? '—'); ?></div>
            <div class="stat-label">Meilleur score</div>
        </div>
        <div class="stat-card">
            <div class="stat-num bad"><?php echo e($stats['worst_score'] ?? '—'); ?></div>
            <div class="stat-label">Score le plus bas</div>
        </div>
    </div>

    <div class="analyses-header">
        <h2>Mes analyses</h2>
    </div>

    <?php if($analyses->isEmpty()): ?>
        <div class="empty-state">
            <div class="empty-icon">📄</div>
            <h3>Aucune analyse pour l'instant</h3>
            <p>Téléchargez votre premier CV pour commencer</p>
            <a href="<?php echo e(route('analysis.create')); ?>" class="btn btn-primary">Analyser mon CV</a>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php $__currentLoopData = $analyses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analysis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="analysis-card">
                <div class="card-top">
                    <div class="card-job">
                        <strong><?php echo e($analysis->job_title); ?></strong>
                        <?php if($analysis->company_name): ?>
                            <span class="company-tag"><?php echo e($analysis->company_name); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-score <?php echo e($analysis->score_color); ?>">
                        <?php echo e($analysis->overall_score); ?>

                    </div>
                </div>

                <div class="mini-scores">
                    <?php $__currentLoopData = ['score_ats'=>'ATS','score_tone'=>'Style','score_content'=>'Contenu','score_structure'=>'Structure','score_skills'=>'Skills']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mini-score-item">
                        <div class="mini-bar-track">
                            <div class="mini-bar-fill <?php echo e(\App\Models\CvAnalysis::colorFor($analysis->$col)); ?>"
                                 style="width:<?php echo e($analysis->$col); ?>%"></div>
                        </div>
                        <span><?php echo e($label); ?> <?php echo e($analysis->$col); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="card-date">
                    <?php echo e($analysis->created_at->format('d/m/Y à H:i')); ?>

                </div>

                <div class="card-actions">
                    <a href="<?php echo e(route('analysis.show', $analysis->id)); ?>" class="btn btn-sm btn-outline">Voir détails</a>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cv_analyzer_laravel\resources\views/dashboard/index.blade.php ENDPATH**/ ?>