<?php

namespace Tests\Feature;

use App\Models\Dispensation;
use App\Models\DispensationType;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_numbers_are_unique_and_sequential(): void
    {
        $this->seed();

        $service = new DocumentService;
        $n1 = $service->nextDocumentNumber();

        $user = User::first();
        $type = DispensationType::first();
        $d = Dispensation::create([
            'applicant_user_id' => $user->id, 'type_id' => $type->id, 'title' => 'T', 'reason' => 'R', 'date_start' => now()->toDateString(), 'status' => 'APPROVED',
        ]);

        $service->generate($d);
        $n2 = $service->nextDocumentNumber();

        $this->assertNotEquals($n1, $n2);
        $this->assertMatchesRegularExpression('/^DSP\/SMK\/\d{2}\/\d{4}\/\d{3}$/', $n1);
    }
}
