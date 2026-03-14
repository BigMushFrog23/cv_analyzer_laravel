# CV Analyzer — Laravel — Guide d'installation XAMPP

## Structure du projet (Architecture MVC Laravel)

```
cv-analyzer-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php      ← Inscription, Connexion, Déconnexion
│   │   │   ├── DashboardController.php ← Tableau de bord
│   │   │   └── AnalysisController.php  ← CRUD complet des analyses
│   │   └── Middleware/
│   │       ├── Authenticate.php        ← Redirige vers /login si non connecté
│   │       └── RedirectIfAuthenticated.php
│   ├── Models/
│   │   ├── User.php                    ← Modèle Eloquent utilisateur
│   │   └── CvAnalysis.php              ← Modèle Eloquent analyse CV
│   ├── Services/
│   │   ├── AiAnalysisService.php       ← Appel API Claude (Anthropic)
│   │   └── PdfTextExtractor.php        ← Extraction texte PDF
│   └── Providers/
│       └── AppServiceProvider.php      ← Enregistrement des services
├── database/
│   └── migrations/
│       ├── ..._create_users_table.php
│       └── ..._create_cv_analyses_table.php
├── resources/views/                    ← Templates Blade
│   ├── layouts/app.blade.php           ← Layout principal (navbar, footer)
│   ├── home.blade.php
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── dashboard/index.blade.php
│   └── analysis/
│       ├── create.blade.php            ← Formulaire analyse
│       ├── show.blade.php              ← Résultats
│       └── edit.blade.php             ← Modifier
├── routes/
│   └── web.php                         ← Toutes les routes
├── public/
│   ├── index.php                       ← Point d'entrée unique
│   ├── .htaccess                       ← Réécriture URL Apache
│   ├── css/style.css
│   └── js/app.js
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── services.php                    ← Clé API Anthropic
│   └── session.php
├── .env.example                        ← À copier en .env
└── composer.json                       ← Dépendances PHP
```

---

## Installation XAMPP étape par étape

### Étape 1 — Installer Composer (si pas encore fait)
Télécharger et installer : https://getcomposer.org/Composer-Setup.exe
Vérifier : ouvrir un terminal et taper `composer --version`

### Étape 2 — Copier le projet dans XAMPP
```
C:\xampp\htdocs\cv-analyzer-laravel\
```

### Étape 3 — Installer les dépendances Laravel
Ouvrir un terminal dans le dossier du projet :
```bash
cd C:\xampp\htdocs\cv-analyzer-laravel
composer install
```
Cela crée le dossier `vendor/` avec tout Laravel dedans.

### Étape 4 — Configurer l'environnement
```bash
copy .env.example .env
php artisan key:generate
```
La commande `key:generate` génère la clé de chiffrement APP_KEY dans le .env.

### Étape 5 — Configurer la base de données
Ouvrir `.env` et vérifier :
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cv_analyzer
DB_USERNAME=root
DB_PASSWORD=
```

### Étape 6 — Créer la base et les tables
```bash
php artisan migrate
```
Cette commande exécute toutes les migrations → crée automatiquement la DB
et les tables `users` et `cv_analyses`.

Ou manuellement dans phpMyAdmin :
1. Créer une base `cv_analyzer`
2. Puis lancer `php artisan migrate`

### Étape 7 — Lier le stockage public
```bash
php artisan storage:link
```
Crée un lien symbolique `public/storage → storage/app/public`
(nécessaire pour accéder aux CVs uploadés)

### Étape 8 — Ajouter la clé API Anthropic
Ouvrir `.env` et remplacer :
```
ANTHROPIC_API_KEY=YOUR_API_KEY_HERE
```
Par votre clé : https://console.anthropic.com/

### Étape 9 — Lancer l'application

**Option A — Serveur intégré PHP (recommandé pour le dev) :**
```bash
php artisan serve
```
Accéder sur : http://127.0.0.1:8000

**Option B — Via XAMPP Apache :**
Accéder sur : http://localhost/cv-analyzer-laravel/public

---

## Comprendre le code : ce qu'il faut savoir expliquer

### 1. Architecture MVC

| Lettre | Rôle | Fichier |
|--------|------|---------|
| **M**odel | Représente une table SQL + logique métier | `app/Models/CvAnalysis.php` |
| **V**iew | Template HTML avec données dynamiques | `resources/views/` |
| **C**ontroller | Reçoit la requête HTTP, appelle le Model, retourne la View | `app/Http/Controllers/` |

### 2. Les Routes (routes/web.php)
```php
// GET /analyze      → affiche le formulaire
Route::get('/analyze', [AnalysisController::class, 'create']);

// POST /analyze     → traite le formulaire (upload + IA + sauvegarde)
Route::post('/analyze', [AnalysisController::class, 'store']);

// GET /analysis/5   → affiche les résultats de l'analyse #5
Route::get('/analysis/{id}', [AnalysisController::class, 'show']);

// PUT /analysis/5   → met à jour l'analyse #5
Route::put('/analysis/{id}', [AnalysisController::class, 'update']);

// DELETE /analysis/5 → supprime l'analyse #5
Route::delete('/analysis/{id}', [AnalysisController::class, 'destroy']);
```

### 3. Eloquent ORM (vs SQL natif)
```php
// SQL natif (ancien projet) :
$stmt = $db->prepare("SELECT * FROM cv_analyses WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();

// Eloquent (Laravel) — même résultat, beaucoup plus lisible :
$analyses = $user->analyses()->orderBy('created_at', 'desc')->get();
```

### 4. CRUD en Laravel

| Opération | Méthode HTTP | URL | Controller |
|-----------|-------------|-----|------------|
| **C**reate (formulaire) | GET | /analyze | `create()` |
| **C**reate (sauvegarde) | POST | /analyze | `store()` |
| **R**ead (liste) | GET | /dashboard | `DashboardController@index()` |
| **R**ead (détail) | GET | /analysis/{id} | `show()` |
| **U**pdate (formulaire) | GET | /analysis/{id}/edit | `edit()` |
| **U**pdate (sauvegarde) | PUT | /analysis/{id} | `update()` |
| **D**elete | DELETE | /analysis/{id} | `destroy()` |

### 5. Authentification Laravel
```php
// Connexion
Auth::attempt(['email' => $email, 'password' => $password]);

// Vérifier si connecté
Auth::check()

// Récupérer l'utilisateur connecté
Auth::user()

// Déconnexion
Auth::logout()
```

### 6. Sécurité
- **CSRF** : `@csrf` dans chaque formulaire → token caché automatique
- **Method Spoofing** : `@method('DELETE')` car HTML ne supporte que GET/POST
- **Passwords** : hashés bcrypt via le cast `'password' => 'hashed'` dans User.php
- **Validation** : `$request->validate([...])` → erreurs automatiques en français
- **Isolation** : `->where('user_id', Auth::id())` → un user ne voit que ses données
- **Middleware 'auth'** : protège toutes les routes du groupe `Route::middleware('auth')`

### 7. Relations Eloquent (Base Relationnelle)
```php
// Dans User.php — relation 1→N (hasMany)
public function analyses() {
    return $this->hasMany(CvAnalysis::class);
}

// Dans CvAnalysis.php — relation N→1 (belongsTo)
public function user() {
    return $this->belongsTo(User::class);
}

// Utilisation :
$analyses = $user->analyses; // SELECT * FROM cv_analyses WHERE user_id = ?
$user = $analysis->user;     // SELECT * FROM users WHERE id = ?
```

### 8. Les Services (Pattern Service Layer)
- `AiAnalysisService` : encapsule l'appel à l'API Claude
- `PdfTextExtractor` : extrait le texte d'un PDF
- Enregistrés dans `AppServiceProvider` → injectés automatiquement dans le contrôleur
- C'est le principe d'**injection de dépendances** (IoC Container)

### 9. Migrations
```bash
php artisan migrate          # Crée les tables
php artisan migrate:rollback # Annule la dernière migration
php artisan migrate:fresh    # Recrée tout (ATTENTION : supprime les données)
```

---

## Commandes Artisan utiles (à connaître)

```bash
php artisan serve            # Démarre le serveur de dev
php artisan migrate          # Exécute les migrations
php artisan storage:link     # Lien symbolique pour les uploads
php artisan route:list       # Liste toutes les routes
php artisan config:clear     # Vide le cache de config
php artisan cache:clear      # Vide le cache
php artisan tinker           # Console REPL interactive
```

---

## Dépannage

| Problème | Solution |
|----------|----------|
| `composer: command not found` | Installer Composer depuis getcomposer.org |
| `php artisan migrate` échoue | Vérifier que MySQL est démarré et DB_DATABASE dans .env |
| Page blanche | `php artisan config:clear` puis vérifier `APP_DEBUG=true` dans .env |
| Erreur 500 | Lire `storage/logs/laravel.log` |
| Upload ne fonctionne pas | Lancer `php artisan storage:link` |
| CSRF token mismatch | Vider les cookies du navigateur |
| Erreur API IA | Vérifier ANTHROPIC_API_KEY dans .env |
