<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ── Afficher le formulaire d'inscription ───────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    // ── Traiter l'inscription ──────────────────────────────
    public function register(Request $request)
    {
        // Validation des données du formulaire
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            // Messages d'erreur en français
            'name.required'      => 'Le nom est obligatoire.',
            'email.required'     => 'L\'email est obligatoire.',
            'email.email'        => 'L\'email n\'est pas valide.',
            'email.unique'       => 'Cet email est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min'       => 'Le mot de passe doit faire au moins 8 caractères.',
        ]);

        // Créer l'utilisateur — le password est hashé automatiquement
        // grâce au cast 'hashed' dans le modèle User
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Connecter automatiquement après inscription
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Bienvenue ' . $user->name . ' !');
    }

    // ── Afficher le formulaire de connexion ────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    // ── Traiter la connexion ───────────────────────────────
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'L\'email est obligatoire.',
            'email.email'       => 'Format email invalide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        // Auth::attempt() vérifie email + password hashé automatiquement
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate(); // Prévention session fixation
            return redirect()->intended(route('dashboard'));
        }

        // Erreur générique (ne pas dire si c'est l'email ou le password)
        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.',
        ])->onlyInput('email');
    }

    // ── Déconnexion ────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
