<?php

namespace App\Http\Requests;

use App\Models\DispensationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentDispensationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        // Siswa hanya untuk dirinya sendiri; guru boleh memilih siswa.
        if ($user->isSiswa()) {
            return true;
        }

        return $user->isGuru() || $user->isAdmin();
    }

    public function rules(): array
    {
        $isOtherType = DispensationType::query()
            ->whereKey($this->integer('type_id'))
            ->where('category', DispensationType::CATEGORY_STUDENT)
            ->whereRaw('LOWER(name) = ?', ['lainnya'])
            ->where('is_active', true)
            ->exists();

        $rules = [
            'type_id' => ['required', Rule::exists('dispensation_types', 'id')->where('category', DispensationType::CATEGORY_STUDENT)->where('is_active', true)],
            'other_type' => [$isOtherType ? 'required' : 'nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string'],
            'date_start' => ['required', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
            'time_start' => ['nullable'],
            'time_end' => ['nullable'],
            'location' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'signatory_ids' => ['nullable', 'array'],
            'signatory_ids.*' => ['integer', 'distinct', Rule::exists('school_signatories', 'id')->where('is_active', true)],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ];

        if ($this->user()?->isGuru() || $this->user()?->isAdmin()) {
            $rules['student_ids'] = ['required', 'array', 'min:1'];
            $rules['student_ids.*'] = ['exists:students,id'];
        } elseif ($this->user()?->isSiswa()) {
            $rules['student_ids'] = ['sometimes', 'array'];
            $rules['student_ids.*'] = ['exists:students,id'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()?->isSiswa() && ! $this->has('student_ids')) {
            $this->merge(['student_ids' => [$this->user()->student?->id]]);
        }
    }
}
