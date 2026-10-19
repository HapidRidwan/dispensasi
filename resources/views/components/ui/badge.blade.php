@props(['status' => 'DRAFT'])

@php
    $styles = match ($status) {
        'APPROVED', 'FINALIZED', 'COMPLETED' => 'bg-green-50 text-green-700 border-green-200',
        'REJECTED' => 'bg-red-50 text-red-700 border-red-200',
        'SUBMITTED', 'ADMIN_REVIEW', 'PDF_GENERATED', 'WAITING_ENDORSEMENT', 'TEACHER_VERIFIED' => 'bg-amber-50 text-amber-700 border-amber-200',
        default => 'bg-slate-50 text-slate-600 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium $styles"]) }}>
    {{ $slot ?? $status }}
</span>
