<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::query()
            ->with('dispensation')
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $query->where('document_number', 'like', '%'.$search.'%')
                    ->orWhere('document_status', 'like', '%'.$search.'%')
                    ->orWhereHas('dispensation', fn ($dispensation) => $dispensation->where('title', 'like', '%'.$search.'%'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.documents.index', compact('documents'));
    }

    public function show(Document $document)
    {
        return view('admin.documents.show', [
            'document' => $document->load(['dispensation', 'endorsements.user']),
        ]);
    }

    public function print(Document $document): View
    {
        return view('admin.documents.print', [
            'document' => $document->load([
                'dispensation.type',
                'dispensation.responsibles.teacher.user',
                'dispensation.recipients.student.user',
            ]),
        ]);
    }

    public function sign(Request $request, Document $document): RedirectResponse
    {
        $document->update(['signed_at' => now(), 'document_status' => Document::STATUS_SIGNED]);

        return back()->with('status', 'Dokumen ditandatangani.');
    }

    public function stamp(Request $request, Document $document): RedirectResponse
    {
        $document->update(['stamped_at' => now(), 'document_status' => Document::STATUS_STAMPED]);

        return back()->with('status', 'Dokumen distempel.');
    }

    public function finalize(Request $request, Document $document): RedirectResponse
    {
        $document->update(['finalized_at' => now(), 'document_status' => Document::STATUS_FINAL]);
        $document->dispensation->update(['status' => 'FINALIZED']);

        return back()->with('status', 'Dokumen difinalisasi.');
    }
}
