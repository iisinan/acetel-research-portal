<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        $templates = DocumentTemplate::latest()->paginate(10);
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB
            'type' => 'required|string',
            'version' => 'required|string',
        ]);

        $path = $request->file('file')->store('templates');

        DocumentTemplate::create([
            'title' => $validated['title'],
            'file_path' => $path,
            'type' => $validated['type'],
            'version' => $validated['version'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.templates.index')->with('success', 'Document template uploaded successfully.');
    }

    public function edit(DocumentTemplate $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, DocumentTemplate $template)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'type' => 'required|string',
            'version' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data = [
            'title' => $validated['title'],
            'type' => $validated['type'],
            'version' => $validated['version'],
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if (Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }
            $data['file_path'] = $request->file('file')->store('templates');
        }

        $template->update($data);

        return redirect()->route('admin.templates.index')->with('success', 'Document template updated successfully.');
    }

    public function destroy(DocumentTemplate $template)
    {
        if (Storage::disk('public')->exists($template->file_path)) {
            Storage::disk('public')->delete($template->file_path);
        }
        $template->delete();
        return redirect()->route('admin.templates.index')->with('success', 'Document template deleted successfully.');
    }

    public function download(DocumentTemplate $template)
    {
        if (!Storage::disk('public')->exists($template->file_path)) {
             return back()->with('error', 'File not found.');
        }
        return Storage::disk('public')->download($template->file_path, $template->title . '.' . pathinfo($template->file_path, PATHINFO_EXTENSION));
    }
}
