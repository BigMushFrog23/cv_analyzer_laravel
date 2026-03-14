<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Les champs qu'on peut remplir en masse (mass assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Les champs cachés lors de la sérialisation (ex: API JSON)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts automatiques
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // Laravel hashe automatiquement avec bcrypt
        ];
    }

    /**
     * Relation : un User a plusieurs CvAnalysis
     * C'est la relation One-to-Many (1 → N)
     */
    public function analyses()
    {
        return $this->hasMany(CvAnalysis::class);
    }
}
