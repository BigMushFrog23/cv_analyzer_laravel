<?php

namespace App\Http\Controllers;

use App\Models\CvAnalysis;
use App\Services\AiAnalysisService; // Make sure this matches your Service file name
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

    public function create()
    {
        return view('analysis.create');
    }

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
        $cvText = $this->pdfExtractor->extract($fullPath);

        if (strlen(trim($cvText)) < 100) {
            Storage::disk('public')->delete($filename);
            return back()->withErrors(['cv_file' => 'Impossible d\'extraire le texte du PDF.']);
        }

        // Call Gemini
        $result = $this->aiService->analyze(
            $cvText,
            $validated['job_title'],
            $validated['job_description'],
            (int) $validated['years_experience']
        );

        if (isset($result['error'])) {
            Storage::disk('public')->delete($filename);
            return back()->withErrors(['cv_file' => 'Erreur IA : ' . $result['error']]);
        }

        $feedback = $result['feedback'];

        $analysis = Auth::user()->analyses()->create([
            'job_title'        => $validated['job_title'],
            'company_name'     => $validated['company_name'] ?? '',
            'job_description'  => $validated['job_description'],
            'years_experience' => $validated['years_experience'],
            'cv_filename'      => $filename,
            'overall_score'    => $feedback['overallScore'] ?? 0,
            'score_ats'        => $feedback['ATS']['score'] ?? 0,
            'score_tone'       => $feedback['toneAndStyle']['score'] ?? 0,
            'score_content'    => $feedback['content']['score'] ?? 0,
            'score_structure'  => $feedback['structure']['score'] ?? 0,
            'score_skills'     => $feedback['skills']['score'] ?? 0,
            'ai_feedback_json' => $feedback,
        ]);

        return redirect()->route('analysis.show', $analysis->id);
    }

    public function show(int $id)
    {
        $analysis = CvAnalysis::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('analysis.show', compact('analysis'));
    }
}