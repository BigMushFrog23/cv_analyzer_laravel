<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Eloquent ORM : récupérer toutes les analyses de l'utilisateur connecté
        // orderBy = ORDER BY created_at DESC en SQL
        $analyses = $user->analyses()
            ->orderBy('created_at', 'desc')
            ->get();

        // Statistiques avec les méthodes d'aggregation Eloquent
        $stats = [
            'total'       => $analyses->count(),
            'avg_score'   => $analyses->avg('overall_score') ? round($analyses->avg('overall_score')) : null,
            'best_score'  => $analyses->max('overall_score'),
            'worst_score' => $analyses->min('overall_score'),
        ];

        return view('dashboard.index', compact('analyses', 'stats'));
    }
}
