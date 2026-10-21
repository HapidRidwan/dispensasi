<?php

use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DataImportController;
use App\Http\Controllers\Admin\DispensationController as AdminDispensationController;
use App\Http\Controllers\Admin\DispensationTypeController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SchoolSignatoryController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\Student\DispensationController as StudentDispensationController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\Teacher\DispensationController as TeacherDispensationController;
use App\Models\Dispensation;
use App\Models\SchoolSignatory;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware([
    'auth',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', fn () => match (auth()->user()->role) {
        'guru' => redirect()->route('guru.dashboard'),
        'siswa' => redirect()->route('siswa.dashboard'),
        default => redirect()->route('admin.dashboard'),
    })->name('dashboard');

    Route::get('/admin/profile', [ProfileController::class, 'show'])->middleware('role:admin')->name('admin.profile');
    Route::get('/guru/profile', [ProfileController::class, 'show'])->middleware('role:guru')->name('guru.profile');
    Route::get('/siswa/profile', [ProfileController::class, 'show'])->middleware('role:siswa')->name('siswa.profile');

    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
    Route::get('/dispensations/{dispensation}/print', [StudentDispensationController::class, 'print'])->name('dispensations.print');
});

Route::middleware(['auth', 'role:admin', 'require-admin-2fa'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/data-import/templates/{dataset}', [DataImportController::class, 'template'])->name('data-import.templates');
    Route::post('/data-import', [DataImportController::class, 'store'])->name('data-import.store');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::post('/students/{student}/toggle-active', [StudentController::class, 'toggleActive'])->name('students.toggle-active');

    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::post('/teachers/{teacher}/toggle-active', [TeacherController::class, 'toggleActive'])->name('teachers.toggle-active');

    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
    Route::put('/classes/{class}', [ClassController::class, 'update'])->name('classes.update');
    Route::post('/classes/{class}/toggle-active', [ClassController::class, 'toggleActive'])->name('classes.toggle-active');

    Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index'])->name('teacher-assignments.index');
    Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store'])->name('teacher-assignments.store');
    Route::delete('/teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'destroy'])->name('teacher-assignments.destroy');

    Route::get('/dispensation-types', [DispensationTypeController::class, 'index'])->name('dispensation-types.index');
    Route::post('/dispensation-types', [DispensationTypeController::class, 'store'])->name('dispensation-types.store');
    Route::put('/dispensation-types/{dispensationType}', [DispensationTypeController::class, 'update'])->name('dispensation-types.update');
    Route::post('/dispensation-types/{dispensationType}/toggle-active', [DispensationTypeController::class, 'toggleActive'])->name('dispensation-types.toggle-active');

    Route::get('/dispensations', [AdminDispensationController::class, 'index'])->name('dispensations.index');
    Route::get('/dispensations/create', [AdminDispensationController::class, 'create'])->name('dispensations.create');
    Route::post('/dispensations', [AdminDispensationController::class, 'store'])->name('dispensations.store');
    Route::get('/activities/create', [AdminDispensationController::class, 'createActivity'])->name('activities.create');
    Route::post('/activities', [AdminDispensationController::class, 'storeActivity'])->name('activities.store');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/print', [DashboardController::class, 'print'])->name('dashboard.print');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/dispensations/{dispensation}', [AdminDispensationController::class, 'show'])->name('dispensations.show');
    Route::post('/dispensations/{dispensation}/approve', [AdminDispensationController::class, 'approve'])->name('dispensations.approve');
    Route::post('/dispensations/{dispensation}/reject', [AdminDispensationController::class, 'reject'])->name('dispensations.reject');
    Route::post('/dispensations/{dispensation}/generate-document', [AdminDispensationController::class, 'generateDocument'])->name('dispensations.generate-document');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}/print', [DocumentController::class, 'print'])->name('documents.print');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])->name('documents.sign');
    Route::post('/documents/{document}/stamp', [DocumentController::class, 'stamp'])->name('documents.stamp');
    Route::post('/documents/{document}/finalize', [DocumentController::class, 'finalize'])->name('documents.finalize');
    Route::get('/school-signatories', [SchoolSignatoryController::class, 'index'])->name('school-signatories.index');
    Route::post('/school-signatories', [SchoolSignatoryController::class, 'store'])->name('school-signatories.store');
    Route::post('/school-signatories/{schoolSignatory}/toggle-active', [SchoolSignatoryController::class, 'toggleActive'])->name('school-signatories.toggle-active');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $teacher = $user->teacher;
        $homeroomClassIds = $teacher?->assignments()
            ->where('assignment_type', TeacherAssignment::TYPE_HOMEROOM)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->select('class_id');
        $relevantDispensations = fn () => Dispensation::query()
            ->where(function ($query) use ($user, $teacher, $homeroomClassIds) {
                $query->where('applicant_user_id', $user->id);

                if ($teacher) {
                    $query->orWhereHas('recipients.student.enrollments', fn ($enrollments) => $enrollments
                        ->where('is_active', true)
                        ->whereIn('class_id', $homeroomClassIds));
                }
            });

        return view('guru.dashboard.index', [
            'needVerification' => Dispensation::where('status', 'SUBMITTED')
                ->whereHas('recipients.student.enrollments', fn ($query) => $query
                    ->where('is_active', true)
                    ->whereIn('class_id', $teacher?->assignments()
                        ->where('assignment_type', TeacherAssignment::TYPE_HOMEROOM)
                        ->where('is_active', true)
                        ->where(fn ($query) => $query->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
                        ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
                        ->pluck('class_id') ?? []))
                ->count(),
            'mine' => Dispensation::where('applicant_user_id', $user->id)->count(),
            'activities' => Dispensation::whereHas('responsibles', fn ($query) => $query->where('teacher_id', $teacher?->id))
                ->whereIn('status', ['ADMIN_REVIEW', 'APPROVED', 'PDF_GENERATED', 'WAITING_ENDORSEMENT', 'FINALIZED'])
                ->count(),
            'today' => Dispensation::whereDate('date_start', today())
                ->whereIn('status', ['APPROVED', 'PDF_GENERATED', 'WAITING_ENDORSEMENT', 'FINALIZED', 'COMPLETED'])
                ->count(),
            'responsible' => Dispensation::with('recipients')
                ->whereHas('responsibles', fn ($query) => $query->where('teacher_id', $teacher?->id))
                ->whereIn('status', ['ADMIN_REVIEW', 'APPROVED', 'PDF_GENERATED', 'WAITING_ENDORSEMENT', 'FINALIZED'])
                ->latest()
                ->limit(5)
                ->get(),
            'latest' => $relevantDispensations()->with('type')->latest()->limit(8)->get(),
        ]);
    })->name('dashboard');
    Route::get('/dispensations', [TeacherDispensationController::class, 'index'])->name('dispensations.index');
    Route::get('/dispensations/create', fn () => view('guru.dispensations.create', [
        'schoolSignatories' => SchoolSignatory::query()->where('is_active', true)->orderBy('position')->orderBy('name')->get(),
    ]))->name('dispensations.create');
    Route::get('/dispensations/{dispensation}', [TeacherDispensationController::class, 'show'])->name('dispensations.show');
    Route::post('/dispensations', [TeacherDispensationController::class, 'store'])->name('dispensations.store');
    Route::post('/dispensations/{dispensation}/verify', [TeacherDispensationController::class, 'verify'])->name('dispensations.verify');
    Route::get('/activities', [TeacherDispensationController::class, 'indexActivities'])->name('activities.index');
    Route::get('/activities/create', fn () => view('guru.activities.create', [
        'schoolSignatories' => SchoolSignatory::query()->where('is_active', true)->orderBy('position')->orderBy('name')->get(),
    ]))->name('activities.create');
    Route::get('/activities/{dispensation}', [TeacherDispensationController::class, 'showActivity'])->name('activities.show');
    Route::post('/activities', [TeacherDispensationController::class, 'storeActivity'])->name('activities.store');
    Route::get('/notifications', [App\Http\Controllers\Guru\NotificationController::class, 'index'])->name('notifications.index');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $studentId = $user->student?->id;
        $visibleDispensations = fn () => Dispensation::query()
            ->where(function ($query) use ($user, $studentId) {
                $query->where('applicant_user_id', $user->id)
                    ->orWhereHas('recipients', fn ($recipients) => $recipients->where('student_id', $studentId));
            });

        return view('siswa.dashboard.index', [
            'active' => $visibleDispensations()->whereIn('status', ['SUBMITTED', 'TEACHER_VERIFIED', 'ADMIN_REVIEW'])->count(),
            'approved' => $visibleDispensations()->whereIn('status', ['APPROVED', 'PDF_GENERATED', 'WAITING_ENDORSEMENT', 'FINALIZED', 'COMPLETED'])->count(),
            'rejected' => $visibleDispensations()->where('status', 'REJECTED')->count(),
            'total' => $visibleDispensations()->count(),
            'latest' => $visibleDispensations()->with('type')->latest()->limit(8)->get(),
        ]);
    })->name('dashboard');
    Route::get('/dispensations', [StudentDispensationController::class, 'index'])->name('dispensations.index');
    Route::get('/dispensations/create', fn () => view('siswa.dispensations.create', [
        'schoolSignatories' => SchoolSignatory::query()->where('is_active', true)->orderBy('position')->orderBy('name')->get(),
    ]))->name('dispensations.create');
    Route::get('/dispensations/{dispensation}', [StudentDispensationController::class, 'show'])->name('dispensations.show');
    Route::post('/dispensations', [StudentDispensationController::class, 'store'])->name('dispensations.store');
    Route::get('/notifications', [StudentNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [StudentNotificationController::class, 'markRead'])->name('notifications.read');
});
