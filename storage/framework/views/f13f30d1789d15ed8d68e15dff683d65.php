<?php $__env->startSection('title', 'CV Analyzer — Connexion'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="<?php echo e(route('home')); ?>" class="back-link">← Retour</a>
            <h2>Connexion</h2>
            <p>Accédez à votre tableau de bord</p>
        </div>

        
        <?php if($errors->any()): ?>
            <div class="alert alert-error">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        
        <form method="POST" action="<?php echo e(route('login')); ?>" class="auth-form">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       value="<?php echo e(old('email')); ?>"
                       placeholder="vous@exemple.fr"
                       class="<?php echo e($errors->has('email') ? 'input-error' : ''); ?>"
                       required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password"
                       placeholder="Votre mot de passe"
                       required>
            </div>

            <div class="form-group form-check" style="margin-top: 15px; margin-bottom: 15px;">
                <label class="check-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: #a0aec0;">
                    <input type="checkbox" name="remember" style="width: 16px; height: 16px; cursor: pointer; margin: 0;">
                    <span style="font-size: 14px; line-height: 1;">Se souvenir de moi</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
        </form>

        <p class="auth-switch">
            Pas encore de compte ?
            <a href="<?php echo e(route('register')); ?>">S'inscrire</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\cv_analyzer_laravel\resources\views\auth\login.blade.php ENDPATH**/ ?>