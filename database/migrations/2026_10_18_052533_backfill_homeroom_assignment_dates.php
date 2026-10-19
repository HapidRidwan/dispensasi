<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $assignments = DB::table('teacher_assignments')
                ->join('classes', 'teacher_assignments.class_id', '=', 'classes.id')
                ->where('teacher_assignments.assignment_type', 'HOMEROOM')
                ->where(fn ($query) => $query
                    ->whereNull('teacher_assignments.start_date')
                    ->orWhereNull('teacher_assignments.end_date'))
                ->get([
                    'teacher_assignments.id',
                    'classes.academic_year',
                ]);

            $datesByAssignment = [];

            foreach ($assignments as $assignment) {
                $academicYear = (string) $assignment->academic_year;

                if (
                    preg_match('/^(\d{4})\s*[\/-]\s*(\d{4})$/', $academicYear, $years) !== 1
                    || (int) $years[2] !== (int) $years[1] + 1
                ) {
                    throw new RuntimeException(
                        "Cannot backfill homeroom assignment {$assignment->id}: invalid academic year [{$academicYear}]."
                    );
                }

                $datesByAssignment[$assignment->id] = [
                    'start_date' => sprintf('%04d-07-01', (int) $years[1]),
                    'end_date' => sprintf('%04d-06-30', (int) $years[2]),
                ];
            }

            foreach ($datesByAssignment as $assignmentId => $dates) {
                DB::table('teacher_assignments')
                    ->where('id', $assignmentId)
                    ->update($dates);
            }
        });
    }

    public function down(): void
    {
        throw new RuntimeException('The original homeroom assignment dates cannot be restored safely.');
    }
};
