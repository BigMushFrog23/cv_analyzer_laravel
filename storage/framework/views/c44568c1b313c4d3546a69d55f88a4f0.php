<?php $__env->startSection('title', 'CV Analyzer — Inscription'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="<?php echo e(route('home')); ?>" class="back-link">← Retour</a>
            <h2>Créer un compte</h2>
            <p>Commencez à analyser vos CVs gratuitement</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-error">
                <ul style="margin:0;padding-left:1.2rem">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('register')); ?>" class="auth-form">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name"
                       value="<?php echo e(old('name')); ?>"
                       placeholder="Jean Dupont"
                       class="<?php echo e($errors->has('name') ? 'input-error' : ''); ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       value="<?php echo e(old('email')); ?>"
                       placeholder="vous@exemple.fr"
                       class="<?php echo e($errors->has('email') ? 'input-error' : ''); ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe <small>(8 caractères min.)</small></label>
                <input type="password" id="password" name="password"
                       placeholder="Créez un mot de passe fort"
                       minlength="8" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       placeholder="Répétez le mot de passe"
                       required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Créer mon compte</button>
        </form>

        <p class="auth-switch">
            Déjà un compte ? <a href="<?php echo e(route('login')); ?>">Se connecter</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel\resources\views/auth/register.blade.php ENDPATH**/ ?>