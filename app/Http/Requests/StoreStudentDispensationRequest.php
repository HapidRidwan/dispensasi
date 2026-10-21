<?php

namespace App\Http\Requests;

use App\Models\DispensationType;
use App\Models\TeacherAssignment;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'permit_direction' => ['nullable', Rule::in(['KELUAR', 'MASUK'])],
            'picket_assignment_id' => Rule::when($this->user()?->isAdmin(), [
                'nullable',
                'integer',
                Rule::exists('teacher_assignments', 'id')->where('assignment_type', TeacherAssignment::TYPE_DUTY)->where('is_active', true),
            ], ['prohibited']),
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

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->filled('picket_assignment_id') || $validator->errors()->hasAny(['date_start', 'picket_assignment_id'])) {
                return;
            }

            $assignment = TeacherAssignment::query()->find($this->integer('picket_assignment_id'));

            if ($assignment && ! $assignment->isScheduledOn(Carbon::parse($this->input('date_start')))) {
                $validator->errors()->add('picket_assignment_id', 'Jadwal guru piket tidak sesuai dengan hari atau tanggal dispensasi.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()?->isSiswa() && ! $this->has('student_ids')) {
            $this->merge(['student_ids' => [$this->user()->student?->id]]);
        }
    }
}
