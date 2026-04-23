<?php

namespace App\Http\Controllers;

use App\Models\AiAgentPrompt;
use Illuminate\Http\Request;

class AiAgentPromptController extends Controller
{
    public function index(Request $request)
    {
        $limit  = $request->get('limit', 10);
        $search = $request->get('search');

        $query = AiAgentPrompt::orderBy('id', 'desc');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('prompt', 'like', "%{$search}%");
        }

        $prompts = $query->paginate($limit)->withQueryString();

        return view('pages.promt ai.index', compact('prompts', 'limit', 'search'));
    }

    public function create()
    {
        return view('pages.promt ai.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'prompt' => 'required|string',
        ]);

        AiAgentPrompt::create($request->only('name', 'prompt'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Template prompt berhasil ditambahkan!']);
        }

        return redirect()->route('ai-prompt.index')
                         ->with('success', 'Template prompt berhasil ditambahkan!');
    }

    public function edit(AiAgentPrompt $prompt)
    {
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'id'     => $prompt->id,
                'name'   => $prompt->name,
                'prompt' => $prompt->prompt,
            ]);
        }

        return view('pages.promt ai.edit', compact('prompt'));
    }

    public function update(Request $request, AiAgentPrompt $prompt)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'prompt' => 'required|string',
        ]);

        $prompt->update($request->only('name', 'prompt'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Template prompt berhasil diperbarui!']);
        }

        return redirect()->route('ai-prompt.index')
                         ->with('success', 'Template prompt berhasil diperbarui!');
    }

    public function destroy(AiAgentPrompt $prompt)
    {
        $prompt->delete();

        return redirect()->route('ai-prompt.index')
                         ->with('success', 'Template prompt berhasil dihapus!');
    }
}
