<?php

namespace App\Http\Controllers;

use App\Models\CvAnalysis;
use App\Services\AiAnalysisService;
use App\Services\PdfTextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AnalysisController extends Controller
{
    /**
     * Injection des services nécessaires via le constructeur
     */
    public function __construct(
        private AiAnalysisService $aiService,
        private PdfTextExtractor  $pdfExtractor,
    ) {}

    /**
     * Affiche la liste de toutes les analyses de l'utilisateur (Dashboard)
     */
    public function index()
    {
        $analyses = Auth::user()->analyses()->latest()->get();
        return view('dashboard', compact('analyses'));
    }

    /**
     * Affiche le formulaire d'upload
     */
    public function create()
    {
        return view('analysis.create');
    }

    /**
     * Traite l'upload du CV et l'analyse par l'IA
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_title'        => ['required', 'string', 'max:200'],
            'company_name'     => ['nullable', 'string', 'max:200'],
            'job_description'  => ['required', 'string', 'min:20'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:30'],
            'cv_file'          => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $filename = $request->file('cv_file')->store('cvs', 'public');
        $fullPath = Storage::disk('public')->path($filename);
        $errorMessage = null;

        try {
            // 1. Text Extraction
            $cvText = $this->pdfExtractor->extract($fullPath);
            if (strlen(trim($cvText)) < 100) {
                throw new \Exception('Le contenu du CV semble trop court ou illisible.');
            }

            // 2. AI Analysis
            $result = $this->aiService->analyze(
                $cvText,
                $validated['job_title'],
                $validated['job_description'],
            );

            if (isset($result['error'])) {
                throw new \Exception('Erreur IA : ' . $result['error']);
            }

            // 3. Save to Database
            $analysis = Auth::user()->analyses()->create([
                'job_title'        => $validated['job_title'],
                'company_name'     => $validated['company_name'] ?? 'Inconnue',
                'job_description'  => $validated['job_description'],
                'years_experience' => $validated['years_experience'],
                'cv_filename'      => $filename,
                'overall_score'    => (int) ($result['feedback']['overallScore'] ?? 0),
                'ai_feedback_json' => $result['feedback'],
                // Map other scores here...
            ]);

        } catch (\Exception $e) {
            Storage::disk('public')->delete($filename);
            $errorMessage = $e->getMessage();
        }

        // Final Return Logic (Only 2 exit points now)
        return $errorMessage 
            ? back()->withErrors(['cv_file' => $errorMessage])
            : redirect()->route('analysis.show', $analysis->id)->with('success', 'Analyse terminée !');
    }

    /**
     * Affiche les détails d'une analyse spécifique
     */
    public function show(int $id)
    {
        $analysis = CvAnalysis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('analysis.show', compact('analysis'));
    }

    /**
     * Supprime une analyse et son fichier associé
     */
    public function destroy(int $id)
    {
        $analysis = CvAnalysis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            // Supprimer le fichier physique du stockage
            if (Storage::disk('public')->exists($analysis->cv_filename)) {
                Storage::disk('public')->delete($analysis->cv_filename);
            }

            $analysis->delete();

            return redirect()->route('dashboard')
                             ->with('success', 'L\'analyse a été supprimée.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression analyse', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Impossible de supprimer cette analyse.');
        }
    }
}
