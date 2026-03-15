<?php $__env->startSection('title', 'CV Analyzer — Analyser mon CV'); ?>

<?php $__env->startSection('content'); ?>
<div class="analyze-container">
    <div class="page-header">
        <a href="<?php echo e(route('dashboard')); ?>" class="back-link">← Tableau de bord</a>
        <h1>Analyser votre CV</h1>
        <p class="text-muted">Renseignez l'offre visée, puis uploadez votre CV en PDF</p>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-error">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('analysis.store')); ?>"
          enctype="multipart/form-data"
          class="analyze-form"
          id="analyzeForm">
        <?php echo csrf_field(); ?>

        <div class="form-section">
            <h3>🎯 L'offre d'emploi</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="job_title">Titre du poste <span class="required">*</span></label>
                    <input type="text" id="job_title" name="job_title"
                           value="<?php echo e(old('job_title')); ?>"
                           placeholder="ex: Développeur Full-Stack"
                           required>
                </div>
                <div class="form-group">
                    <label for="company_name">Entreprise</label>
                    <input type="text" id="company_name" name="company_name"
                           value="<?php echo e(old('company_name')); ?>"
                           placeholder="ex: Google (optionnel)">
                </div>
            </div>
            <div class="form-group">
                <label for="years_experience">Années d'expérience requises</label>
                <input type="number" id="years_experience" name="years_experience"
                       value="<?php echo e(old('years_experience', 0)); ?>"
                       min="0" max="30">
            </div>
            <div class="form-group">
                <label for="job_description">Description du poste <span class="required">*</span></label>
                <textarea id="job_description" name="job_description" rows="6"
                          placeholder="Collez ici la description complète du poste..."
                          required><?php echo e(old('job_description')); ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>📄 Votre CV</h3>
            <div class="upload-zone" id="uploadZone">
                <input type="file" id="cv_file" name="cv_file"
                       accept=".pdf" required class="file-input">
                <div class="upload-icon">⬆</div>
                <p class="upload-text">Glissez votre CV ici ou <strong>cliquez pour sélectionner</strong></p>
                <p class="upload-hint">Format PDF uniquement — 5 Mo max</p>
                <div class="file-preview" id="filePreview" style="display:none">
                    <span class="file-icon">📄</span>
                    <span id="fileName"></span>
                    <button type="button" class="file-remove" onclick="clearFile()" aria-label="Remove file">
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" id="submitBtn">
            <span class="btn-text">✦ Analyser mon CV</span>
            <span class="btn-loading" style="display:none">⏳ Analyse en cours (30-60s)...</span>
        </button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cv_analyzer_laravel\resources\views/analysis/create.blade.php ENDPATH**/ ?>