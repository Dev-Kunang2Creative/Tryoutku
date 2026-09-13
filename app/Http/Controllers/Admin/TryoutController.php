<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TryoutController extends Controller
{
    public function index()
    {
        $tryouts = Tryout::withCount(['questions', 'examAttempts'])
            ->latest()
            ->paginate(10);

        return view('admin.tryouts.index', compact('tryouts'));
    }

    public function create()
    {
        if (Tryout::count() >= Tryout::MAX_PACKAGES) {
            return redirect()->route('admin.tryouts.index')
                ->with('error', 'Maksimal '.Tryout::MAX_PACKAGES.' paket latihan. Hapus atau ubah paket yang ada terlebih dahulu.');
        }

        return view('admin.tryouts.create');
    }

    public function store(Request $request)
    {
        if (Tryout::count() >= Tryout::MAX_PACKAGES) {
            return redirect()->route('admin.tryouts.index')
                ->with('error', 'Maksimal '.Tryout::MAX_PACKAGES.' paket latihan.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:100'],
            'session_type' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:300'],
            'questions_per_session' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(5);
        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = auth()->id();

        $tryout = Tryout::create($validated);

        return redirect()->route('admin.tryouts.questions.index', $tryout)
            ->with('success', 'Paket latihan dibuat. Silakan tambahkan butir soal.');
    }

    public function edit(Tryout $tryout)
    {
        return view('admin.tryouts.edit', compact('tryout'));
    }

    public function update(Request $request, Tryout $tryout)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:100'],
            'session_type' => ['required', 'string', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:300'],
            'questions_per_session' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $tryout->update($validated);

        return redirect()->route('admin.tryouts.index')
            ->with('success', 'Paket latihan diperbarui.');
    }

    public function destroy(Tryout $tryout)
    {
        $tryout->delete();

        return redirect()->route('admin.tryouts.index')
            ->with('success', 'Paket latihan dihapus.');
    }
}
