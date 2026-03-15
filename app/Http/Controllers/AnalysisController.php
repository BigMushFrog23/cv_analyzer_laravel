<?php

namespace App\Http\Controllers;

use App\Models\CvAnalysis;
use App\Services\AiAnalysisService;
use App\Services\PdfTextExtractor;
use App\Exceptions\Analysis\InvalidCvContentException;
use App\Exceptions\Analysis\AiServiceException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AnalysisController extends Controller
{
    /**
     * Injection des services via le constructeur
     */
    public function __construct(
        private AiAnalysisService $aiService,
        private PdfTextExtractor  $pdfExtractor,
    ) {}

    /**
     * Dashboard : Liste des analyses
     */
    public function index()
    {
        $analyses = Auth::user()->analyses()->latest()->get();
        return view('dashboard', compact('analyses'));
    }

    /**
     * Formulaire d'upload
     */
    public function create()
    {
        return view('analysis.create');
    }

    /**
     * Traite l'analyse (Refactored for Clean Code)
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
            // 1. Extraction et Validation du texte
            $cvText = $this->pdfExtractor->extract($fullPath);
            
            if (strlen(trim($cvText)) < 100) {
                throw new InvalidCvContentException('Le contenu du CV semble trop court ou illisible.');
            }

            // 2. Analyse via Service IA
            $result = $this->aiService->analyze(
                $cvText,
                $validated['job_title'],
                $validated['job_description'],
            );

            if (isset($result['error'])) {
                throw new AiServiceException('Erreur IA : ' . $result['error']);
            }

            // 3. Sauvegarde Database
            $analysis = Auth::user()->analyses()->create([
                'job_title'        => $validated['job_title'],
                'company_name'     => $validated['company_name'] ?? 'Inconnue',
                'job_description'  => $validated['job_description'],
                'years_experience' => $validated['years_experience'],
                'cv_filename'      => $filename,
                'overall_score'    => (int) ($result['feedback']['overallScore'] ?? 0),
                'ai_feedback_json' => $result['feedback'],
            ]);

        } catch (InvalidCvContentException | AiServiceException $e) {
            // Utilisation de $e pour le log (Variable utilisée !)
            Log::warning('Échec métier de l\'analyse', ['message' => $e->getMessage()]);
            Storage::disk('public')->delete($filename);
            $errorMessage = $e->getMessage();

        } catch (\Exception $e) {
            // Catch des erreurs système imprévues
            Log::error('Erreur système critique', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Storage::disk('public')->delete($filename);
            $errorMessage = "Une erreur technique est survenue.";
        }

        return $errorMessage 
            ? back()->withErrors(['cv_file' => $errorMessage])
            : redirect()->route('analysis.show', $analysis->id)->with('success', 'Analyse terminée !');
    }

    /**
     * Détails d'une analyse (Sécurisé par user_id)
     */
    public function show(int $id)
    {
        $analysis = CvAnalysis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('analysis.show', compact('analysis'));
    }

    /**
     * Suppression (Sécurisée avec Try/Catch)
     */
    public function destroy(int $id)
    {
        $analysis = CvAnalysis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            if (Storage::disk('public')->exists($analysis->cv_filename)) {
                Storage::disk('public')->delete($analysis->cv_filename);
            }

            $analysis->delete();

            return redirect()->route('dashboard')->with('success', 'Analyse supprimée.');
            
        } catch (\Exception $e) {
            Log::error('Erreur suppression', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Impossible de supprimer cette analyse.');
        }
    }
}