<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\Tryout;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function index(Request $request)
    {
        $query = ExamAttempt::with(['tryout'])
            ->where('status', 'completed');

        if ($request->filled('tryout_id')) {
            $query->where('tryout_id', $request->tryout_id);
        }

        $results = $query->latest('completed_at')->paginate(20)->withQueryString();
        $tryouts = Tryout::orderBy('id')->get();

        return view('admin.results.index', compact('results', 'tryouts'));
    }

    public function show(ExamAttempt $attempt)
    {
        $attempt->load([
            'user',
            'tryout',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        return view('admin.results.show', [
            'attempt' => $attempt,
            'answers' => $attempt->answers,
            'breakdown' => $attempt->answerBreakdown(),
        ]);
    }
}
