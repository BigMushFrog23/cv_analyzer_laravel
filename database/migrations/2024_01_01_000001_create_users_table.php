<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration = version control de la base de données
 * php artisan migrate  → exécute up()
 * php artisan migrate:rollback → exécute down()
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();                           // INT AUTO_INCREMENT PRIMARY KEY
            $table->string('name', 100);
            $table->string('email', 150)->unique(); // UNIQUE INDEX
            $table->string('password');             // bcrypt hash
            $table->rememberToken();                // pour "Se souvenir de moi"
            $table->timestamps();                   // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
