<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    private const PER_PAGE = 15;

    /**
     * Bank soal bisa memuat puluhan butir, jadi daftarnya dipotong per halaman
     * dan bisa disaring lewat kata kunci agar satu soal tidak perlu dicari
     * dengan menggulir seluruh daftar.
     */
    public function index(Request $request, Tryout $tryout)
    {
        $keyword = trim((string) $request->query('q', ''));

        $questions = $tryout->questions()
            ->with('options')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($match) use ($keyword) {
                    $match->where('question_text', 'like', '%'.$keyword.'%')
                        ->orWhere('explanation', 'like', '%'.$keyword.'%')
                        ->orWhereHas('options', fn ($option) => $option->where('option_text', 'like', '%'.$keyword.'%'));
                });
            })
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.questions.index', [
            'tryout' => $tryout,
            'questions' => $questions,
            'keyword' => $keyword,
            'totalQuestions' => $tryout->questions()->count(),
        ]);
    }

    public function create(Tryout $tryout)
    {
        $nextOrder = $tryout->questions()->max('order') + 1;

        return view('admin.questions.create', compact('tryout', 'nextOrder'));
    }

    public function store(Request $request, Tryout $tryout)
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'score_weight' => ['required', 'numeric', 'min:0.1'],
            'order' => ['nullable', 'integer'],
            'options' => ['required', 'array', 'size:5'],
            'options.*' => ['required', 'string'],
            'correct_option' => ['required', 'in:A,B,C,D,E'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $tryout, $validated) {
            $question = $tryout->questions()->create([
                'question_text' => $validated['question_text'],
                'explanation' => $validated['explanation'] ?? null,
                'score_weight' => $validated['score_weight'],
                'order' => $validated['order'] ?? ($tryout->questions()->max('order') + 1),
            ]);

            if ($request->hasFile('image')) {
                $question->replaceImage($request->file('image'));
            }

            foreach ($validated['options'] as $key => $text) {
                $question->options()->create([
                    'option_key' => $key,
                    'option_text' => $text,
                    'is_correct' => ($key === $validated['correct_option']),
                ]);
            }
        });

        return redirect()->route('admin.tryouts.questions.index', $tryout)
            ->with('success', 'Butir soal olimpiade dan opsi pilihan ganda berhasil disimpan!');
    }

    public function edit(Tryout $tryout, Question $question)
    {
        $question->load('options');

        return view('admin.questions.edit', compact('tryout', 'question'));
    }

    public function update(Request $request, Tryout $tryout, Question $question)
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'score_weight' => ['required', 'numeric', 'min:0.1'],
            'order' => ['nullable', 'integer'],
            'options' => ['required', 'array', 'size:5'],
            'options.*' => ['required', 'string'],
            'correct_option' => ['required', 'in:A,B,C,D,E'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $question, $validated) {
            $question->update([
                'question_text' => $validated['question_text'],
                'explanation' => $validated['explanation'] ?? null,
                'score_weight' => $validated['score_weight'],
                'order' => $validated['order'] ?? $question->order,
            ]);

            if ($request->hasFile('image')) {
                $question->replaceImage($request->file('image'));
            } elseif ($request->boolean('remove_image')) {
                $question->removeImage();
            }

            foreach ($validated['options'] as $key => $text) {
                QuestionOption::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'option_key' => $key,
                    ],
                    [
                        'option_text' => $text,
                        'is_correct' => ($key === $validated['correct_option']),
                    ]
                );
            }
        });

        return redirect()->route('admin.tryouts.questions.index', $tryout)
            ->with('success', 'Butir soal berhasil diperbarui!');
    }

    public function destroy(Tryout $tryout, Question $question)
    {
        $question->delete();

        return redirect()->route('admin.tryouts.questions.index', $tryout)
            ->with('success', 'Butir soal berhasil dihapus.');
    }
}
