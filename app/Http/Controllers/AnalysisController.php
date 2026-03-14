<?php

namespace App\Http\Controllers;

use App\Models\CvAnalysis;
use App\Services\AiAnalysisService;
use App\Services\PdfTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnalysisController extends Controller
{
    public function __construct(
        private AiAnalysisService $aiService,
        private PdfTextExtractor  $pdfExtractor,
    ) {}

    // ── CREATE : afficher le formulaire ───────────────────
    public function create()
    {
        return view('analysis.create');
    }

    // ── STORE : traiter l'upload + appel IA + sauvegarde ──
    public function store(Request $request)
    {
        // Validation Laravel — bien plus propre qu'avec PHP natif
        $validated = $request->validate([
            'job_title'        => ['required', 'string', 'max:200'],
            'company_name'     => ['nullable', 'string', 'max:200'],
            'job_description'  => ['required', 'string', 'min:20'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:30'],
            'cv_file'          => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5 Mo max
        ], [
            'job_title.required'       => 'Le titre du poste est obligatoire.',
            'job_description.required' => 'La description du poste est obligatoire.',
            'job_description.min'      => 'La description doit faire au moins 20 caractères.',
            'cv_file.required'         => 'Veuillez uploader votre CV en PDF.',
            'cv_file.mimes'            => 'Seuls les fichiers PDF sont acceptés.',
            'cv_file.max'              => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        // Stocker le PDF dans storage/app/public/cvs/
        $filename = $request->file('cv_file')->store('cvs', 'public');

        // Chemin absolu pour l'extraction de texte
        $fullPath = Storage::disk('public')->path($filename);

        // Extraire le texte du PDF
        $cvText = $this->pdfExtractor->extract($fullPath);

        if (strlen(trim($cvText)) < 100) {
            Storage::disk('public')->delete($filename);
            return back()
                ->withInput()
                ->withErrors(['cv_file' => 'Impossible d\'extraire le texte du PDF. Utilisez un PDF avec du texte sélectionnable (pas scanné).']);
        }

        // Appel à l'API Claude
        $result = $this->aiService->analyze(
            $cvText,
            $validated['job_title'],
            $validated['job_description'],
            (int) $validated['years_experience']
        );

        if (isset($result['error'])) {
            Storage::disk('public')->delete($filename);
            return back()
                ->withInput()
                ->withErrors(['cv_file' => 'Erreur IA : ' . $result['error']]);
        }

        $feedback = $result['feedback'];

        // Sauvegarder en base avec Eloquent (create = INSERT INTO)
        $analysis = Auth::user()->analyses()->create([
            'job_title'        => $validated['job_title'],
            'company_name'     => $validated['company_name'] ?? '',
            'job_description'  => $validated['job_description'],
            'years_experience' => $validated['years_experience'],
            'cv_filename'      => $filename,
            'overall_score'    => $feedback['overallScore']            ?? 0,
            'score_ats'        => $feedback['ATS']['score']            ?? 0,
            'score_tone'       => $feedback['toneAndStyle']['score']   ?? 0,
            'score_content'    => $feedback['content']['score']        ?? 0,
            'score_structure'  => $feedback['structure']['score']      ?? 0,
            'score_skills'     => $feedback['skills']['score']         ?? 0,
            'ai_feedback_json' => $feedback,   // cast 'array' s'occupe du json_encode
        ]);

        return redirect()->route('analysis.show', $analysis->id)
            ->with('success', 'Analyse terminée !');
    }

    // ── READ : afficher le résultat d'une analyse ──────────
    public function show(int $id)
    {
        // findOrFail = SELECT WHERE id = ? (404 si pas trouvé)
        // where user_id = vérifie que l'analyse appartient à l'utilisateur connecté
        $analysis = CvAnalysis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('analysis.show', compact('analysis'));
    }

    // ── EDIT : afficher le formulaire de modification ──────
    public function edit(int $id)
    {
        $analysis = CvAnalysis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('analysis.edit', compact('analysis'));
    }

    // ── UPDATE : sauvegarder les modifications ─────────────
    public function update(Request $request, int $id)
    {
        $analysis = CvAnalysis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'job_title'        => ['required', 'string', 'max:200'],
            'company_name'     => ['nullable', 'string', 'max:200'],
            'job_description'  => ['required', 'string', 'min:20'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:30'],
        ]);

        // Eloquent update = UPDATE SET ... WHERE id = ?
        $analysis->update($validated);

        return redirect()->route('analysis.show', $analysis->id)
            ->with('success', 'Analyse mise à jour avec succès.');
    }

    // ── DESTROY : supprimer une analyse ────────────────────
    public function destroy(int $id)
    {
        $analysis = CvAnalysis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Supprimer le fichier PDF du stockage
        Storage::disk('public')->delete($analysis->cv_filename);

        // Eloquent delete = DELETE FROM cv_analyses WHERE id = ?
        $analysis->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Analyse supprimée.');
    }
}
