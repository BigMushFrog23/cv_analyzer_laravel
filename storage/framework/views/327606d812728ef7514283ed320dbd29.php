<?php $__env->startSection('title', 'CV Analyzer — Modifier'); ?>

<?php $__env->startSection('content'); ?>
<div class="analyze-container">
    <div class="page-header">
        <a href="<?php echo e(route('analysis.show', $analysis->id)); ?>" class="back-link">← Retour au résultat</a>
        <h1>Modifier l'analyse</h1>
        <p class="text-muted">Modifiez les informations du poste (le score IA ne sera pas recalculé)</p>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-error">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    
    <form method="POST" action="<?php echo e(route('analysis.update', $analysis->id)); ?>" class="analyze-form">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="form-section">
            <h3>🎯 Informations du poste</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="job_title">Titre du poste <span class="required">*</span></label>
                    <input type="text" id="job_title" name="job_title"
                           value="<?php echo e(old('job_title', $analysis->job_title)); ?>" required>
                </div>
                <div class="form-group">
                    <label for="company_name">Entreprise</label>
                    <input type="text" id="company_name" name="company_name"
                           value="<?php echo e(old('company_name', $analysis->company_name)); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="years_experience">Années d'expérience requises</label>
                <input type="number" id="years_experience" name="years_experience"
                       value="<?php echo e(old('years_experience', $analysis->years_experience)); ?>"
                       min="0" max="30">
            </div>
            <div class="form-group">
                <label for="job_description">Description du poste <span class="required">*</span></label>
                <textarea id="job_description" name="job_description" rows="6" required><?php echo e(old('job_description', $analysis->job_description)); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="<?php echo e(route('analysis.show', $analysis->id)); ?>" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cv_analyzer_laravel\resources\views\analysis\edit.blade.php ENDPATH**/ ?>