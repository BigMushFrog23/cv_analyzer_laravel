<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // ── Afficher la page "Mon compte" ──────────────────────
    public function edit()
    {
        return view('account.edit');
    }

    // ── Supprimer définitivement le compte ─────────────────
    public function destroy(Request $request)
    {
        // Sécurité : on exige le mot de passe actuel pour confirmer
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required'         => 'Veuillez saisir votre mot de passe pour confirmer.',
            'password.current_password' => 'Le mot de passe est incorrect.',
        ]);

        $user = Auth::user();

        // 1. Supprimer les fichiers CV stockés sur le disque
        //    (les lignes en base partent en cascade grâce à la clé étrangère)
        foreach ($user->analyses as $analysis) {
            if ($analysis->cv_filename && Storage::disk('public')->exists($analysis->cv_filename)) {
                Storage::disk('public')->delete($analysis->cv_filename);
            }
        }

        // 2. Déconnexion + nettoyage de la session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Suppression du compte
        $user->delete();

        return redirect()->route('home')->with('success', 'Votre compte a été supprimé. À bientôt !');
    }
}
