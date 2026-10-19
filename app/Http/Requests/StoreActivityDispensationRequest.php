<?php

namespace App\Http\Requests;

use App\Models\DispensationType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityDispensationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isGuru() || $this->user()->isAdmin());
    }

    public function rules(): array
    {
        $isOtherType = DispensationType::query()
            ->whereKey($this->integer('type_id'))
            ->where('category', DispensationType::CATEGORY_ACTIVITY)
            ->whereRaw('LOWER(name) = ?', ['lainnya'])
            ->where('is_active', true)
            ->exists();

        return [
            'type_id' => ['required', Rule::exists('dispensation_types', 'id')->where('category', DispensationType::CATEGORY_ACTIVITY)->where('is_active', true)],
            'other_type' => [$isOtherType ? 'required' : 'nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string'],
            'date_start' => ['required', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
            'time_start' => ['nullable'],
            'time_end' => ['nullable'],
            'location' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
            'signatory_ids' => ['nullable', 'array'],
            'signatory_ids.*' => ['integer', 'distinct', Rule::exists('school_signatories', 'id')->where('is_active', true)],
            'responsibles' => ['nullable', 'array'],
            'responsibles.*.teacher_id' => ['nullable', 'exists:teachers,id'],
            'responsibles.*.responsibility_type' => ['required', 'in:PENANGGUNG_JAWAB,PEMBIMBING,PENDAMPING'],
            'responsibles.*.notes' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        if (! $this->user()?->isAdmin()) {
            return;
        }

        $validator->sometimes('responsibles', ['required', 'array', 'min:1'], fn () => true);
        $validator->after(function (Validator $validator): void {
            $responsibles = $this->input('responsibles', []);
            $hasAssignedTeacher = is_array($responsibles)
                && collect($responsibles)->contains(fn ($responsible) => is_array($responsible) && filled($responsible['teacher_id'] ?? null));

            if (! $hasAssignedTeacher) {
                $validator->errors()->add('responsibles', 'Pilih minimal satu guru penanggung jawab.');
            }
        });
    }
}
