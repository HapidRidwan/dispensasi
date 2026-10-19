<?php

namespace App\Services;

use App\Models\Dispensation;
use App\Models\Document;
use App\Models\DocumentEndorsement;
use Illuminate\Support\Carbon;

class DocumentService
{
    public function nextDocumentNumber(): string
    {
        $prefix = 'DSP/SMK/'.Carbon::now()->format('m/Y');

        $sequence = Document::where('document_number', 'like', $prefix.'/%')->count() + 1;

        do {
            $number = $prefix.'/'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $exists = Document::where('document_number', $number)->exists();
            $sequence++;
        } while ($exists);

        return $number;
    }

    public function generate(Dispensation $dispensation): Document
    {
        $number = $dispensation->document_number ?: $this->nextDocumentNumber();

        $document = Document::updateOrCreate(
            ['dispensation_id' => $dispensation->id],
            [
                'document_number' => $number,
                'document_type' => 'DISPENSASI',
                'document_status' => Document::STATUS_GENERATED,
                'generated_at' => now(),
            ]
        );

        $dispensation->update(['document_number' => $number]);

        return $document;
    }

    public function endorse(Document $document, int $userId, string $type, ?string $notes = null): DocumentEndorsement
    {
        return $document->endorsements()->create([
            'user_id' => $userId,
            'endorsement_type' => $type,
            'status' => 'APPROVED',
            'notes' => $notes,
            'endorsed_at' => now(),
        ]);
    }
}
