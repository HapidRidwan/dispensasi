<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DispensationType;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetReaderException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataImportController extends Controller
{
    private const DATASETS = [
        'students' => [
            'filename' => 'template-siswa.xlsx',
            'headers' => ['Nama', 'Username', 'Password', 'Email', 'NIS', 'NISN', 'Jenis Kelamin', 'Telepon', 'Alamat', 'Kelas', 'Tahun Ajaran'],
            'aliases' => [
                'nama' => 'name', 'name' => 'name',
                'namalengkap' => 'name',
                'username' => 'username', 'password' => 'password',
                'email' => 'email', 'emailpemulihan' => 'email',
                'nis' => 'nis', 'nisn' => 'nisn',
                'jeniskelamin' => 'gender', 'gender' => 'gender',
                'telepon' => 'phone', 'nomortelepon' => 'phone', 'phone' => 'phone',
                'alamat' => 'address', 'address' => 'address',
                'kelas' => 'class', 'namakelas' => 'class',
                'tahunajaran' => 'academic_year',
            ],
            'required' => ['name', 'username', 'password', 'nis'],
        ],
        'teachers' => [
            'filename' => 'template-guru.xlsx',
            'headers' => ['Nama', 'Username', 'Password', 'Email', 'NIP', 'Telepon', 'Jabatan'],
            'aliases' => [
                'nama' => 'name', 'name' => 'name',
                'namalengkap' => 'name',
                'username' => 'username', 'password' => 'password',
                'email' => 'email', 'emailpemulihan' => 'email',
                'nip' => 'nip', 'telepon' => 'phone', 'nomortelepon' => 'phone', 'phone' => 'phone',
                'jabatan' => 'position', 'position' => 'position',
            ],
            'required' => ['name', 'username', 'password'],
        ],
        'classes' => [
            'filename' => 'template-kelas.xlsx',
            'headers' => ['Nama Kelas', 'Tingkat', 'Jurusan', 'Tahun Ajaran'],
            'aliases' => [
                'nama' => 'name', 'namakelas' => 'name', 'name' => 'name',
                'tingkat' => 'grade', 'grade' => 'grade',
                'jurusan' => 'major', 'major' => 'major',
                'tahunajaran' => 'academic_year',
            ],
            'required' => ['name', 'grade', 'academic_year'],
        ],
        'dispensation-types' => [
            'filename' => 'template-jenis-dispensasi.xlsx',
            'headers' => ['Nama', 'Kategori', 'Deskripsi'],
            'aliases' => [
                'nama' => 'name', 'name' => 'name',
                'kategori' => 'category', 'category' => 'category',
                'deskripsi' => 'description', 'description' => 'description',
            ],
            'required' => ['name', 'category'],
        ],
    ];

    public function template(string $dataset): StreamedResponse
    {
        abort_unless(isset(self::DATASETS[$dataset]), 404);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([self::DATASETS[$dataset]['headers']], null, 'A1');

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            self::DATASETS[$dataset]['filename'],
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dataset' => ['required', Rule::in(array_keys(self::DATASETS))],
            'file' => ['required', 'file', 'mimes:xlsx', 'extensions:xlsx', 'max:10240'],
        ]);

        $reader = IOFactory::createReader('Xlsx');

        if (! $reader->canRead($validated['file']->getRealPath())) {
            throw ValidationException::withMessages(['file' => 'File tidak dapat dibaca sebagai Excel .xlsx.']);
        }

        try {
            $sheetRows = $reader->load($validated['file']->getRealPath())->getActiveSheet()->toArray(null, true, false, false);
        } catch (SpreadsheetReaderException) {
            throw ValidationException::withMessages(['file' => 'File Excel rusak atau formatnya tidak valid.']);
        }

        if (count($sheetRows) < 2 || count($sheetRows) > 5001) {
            throw ValidationException::withMessages(['file' => 'File harus memiliki header dan maksimal 5.000 baris data.']);
        }

        $dataset = $validated['dataset'];
        $fieldByColumn = $this->mapHeaders(array_shift($sheetRows), self::DATASETS[$dataset]);
        $rows = $this->mapRows($sheetRows, $fieldByColumn);
        $preparedRows = $this->validateRows($dataset, $rows);

        $processed = DB::transaction(function () use ($dataset, $preparedRows): int {
            foreach ($preparedRows as $row) {
                $this->persistRow($dataset, $row);
            }

            return count($preparedRows);
        });

        return back()->with('status', "{$processed} baris data berhasil diimpor.");
    }

    /**
     * @param  array<int, mixed>  $headers
     * @param  array{aliases: array<string, string>, required: array<int, string>}  $dataset
     * @return array<int, string>
     */
    private function mapHeaders(array $headers, array $dataset): array
    {
        $fieldByColumn = [];
        $seenFields = [];

        foreach ($headers as $column => $header) {
            $headerText = $this->stringValue($header) ?? '';
            $normalizedHeader = $this->normalizeHeader($headerText);

            if ($normalizedHeader === '') {
                continue;
            }

            $field = $dataset['aliases'][$normalizedHeader] ?? null;

            if ($field === null) {
                throw ValidationException::withMessages(['file' => 'Kolom "'.$headerText.'" tidak dikenal. Gunakan template Excel yang disediakan.']);
            }

            if (isset($seenFields[$field])) {
                throw ValidationException::withMessages(['file' => 'Kolom '.$headerText.' tercantum lebih dari satu kali.']);
            }

            $fieldByColumn[$column] = $field;
            $seenFields[$field] = true;
        }

        foreach ($dataset['required'] as $requiredField) {
            if (! in_array($requiredField, $fieldByColumn, true)) {
                throw ValidationException::withMessages(['file' => 'Kolom wajib belum lengkap. Unduh template untuk melihat judul kolom yang benar.']);
            }
        }

        return $fieldByColumn;
    }

    /**
     * @param  array<int, array<int, mixed>>  $sheetRows
     * @param  array<int, string>  $fieldByColumn
     * @return array<int, array<string, string|null>>
     */
    private function mapRows(array $sheetRows, array $fieldByColumn): array
    {
        $rows = [];

        foreach ($sheetRows as $rowIndex => $sheetRow) {
            $row = [];

            foreach ($fieldByColumn as $column => $field) {
                $row[$field] = $this->stringValue($sheetRow[$column] ?? null);
            }

            if (count(array_filter($row, fn (?string $value): bool => $value !== null)) > 0) {
                $row['_row'] = (string) ($rowIndex + 2);
                $rows[] = $row;
            }
        }

        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'File tidak memiliki baris data untuk diimpor.']);
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, string|null>>  $rows
     * @return array<int, array<string, string|null>>
     */
    private function validateRows(string $dataset, array $rows): array
    {
        $preparedRows = [];
        $seenUniqueValues = [];
        $errors = [];

        foreach ($rows as $row) {
            $rowNumber = $row['_row'];
            unset($row['_row']);

            $row['username'] = isset($row['username']) ? Str::lower($row['username']) : null;
            $row['email'] = $row['email'] ?? null;
            $row['email'] = $row['email'] !== null ? Str::lower($row['email']) : null;
            $row['nisn'] = $row['nisn'] ?? null;
            $row['nip'] = $row['nip'] ?? null;

            if ($dataset === 'dispensation-types' && isset($row['category'])) {
                $row['category'] = match (Str::lower($row['category'])) {
                    'siswa', DispensationType::CATEGORY_STUDENT => DispensationType::CATEGORY_STUDENT,
                    'kegiatan', DispensationType::CATEGORY_ACTIVITY => DispensationType::CATEGORY_ACTIVITY,
                    default => $row['category'],
                };
            }

            $rules = match ($dataset) {
                'students' => [
                    'name' => ['required', 'string', 'max:255'],
                    'username' => ['required', 'string', 'max:255', 'unique:users,username'],
                    'password' => ['required', 'string', 'min:8'],
                    'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
                    'nis' => ['required', 'string', 'max:50', 'unique:students,nis'],
                    'nisn' => ['nullable', 'string', 'max:50'],
                    'gender' => ['nullable', 'string', 'max:20'],
                    'phone' => ['nullable', 'string', 'max:30'],
                    'address' => ['nullable', 'string'],
                    'class' => ['nullable', 'string', 'max:255'],
                    'academic_year' => ['nullable', 'string', 'max:20'],
                ],
                'teachers' => [
                    'name' => ['required', 'string', 'max:255'],
                    'username' => ['required', 'string', 'max:255', 'unique:users,username'],
                    'password' => ['required', 'string', 'min:8'],
                    'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
                    'nip' => ['nullable', 'string', 'max:50', 'unique:teachers,nip'],
                    'phone' => ['nullable', 'string', 'max:30'],
                    'position' => ['nullable', 'string', 'max:100'],
                ],
                'classes' => [
                    'name' => ['required', 'string', 'max:255'],
                    'grade' => ['required', 'string', 'max:10'],
                    'major' => ['nullable', 'string', 'max:50'],
                    'academic_year' => ['required', 'string', 'max:20'],
                ],
                'dispensation-types' => [
                    'name' => ['required', 'string', 'max:255'],
                    'category' => ['required', 'string', Rule::in([DispensationType::CATEGORY_STUDENT, DispensationType::CATEGORY_ACTIVITY])],
                    'description' => ['nullable', 'string'],
                ],
            };

            $validator = Validator::make($row, $rules);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$rowNumber}: {$message}";
                }

                continue;
            }

            foreach ($this->uniqueFields($dataset) as $field => $table) {
                $value = $row[$field] ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                $uniqueKey = $table.':'.$field.':'.Str::lower($value);

                if (isset($seenUniqueValues[$uniqueKey])) {
                    $errors[] = "Baris {$rowNumber}: nilai {$field} duplikat dengan baris {$seenUniqueValues[$uniqueKey]}.";
                } else {
                    $seenUniqueValues[$uniqueKey] = $rowNumber;
                }
            }

            if ($dataset === 'students' && filled($row['class'] ?? null)) {
                $classQuery = SchoolClass::query()->where('name', $row['class'])->where('is_active', true);

                if (filled($row['academic_year'] ?? null)) {
                    $classQuery->where('academic_year', $row['academic_year']);
                }

                $classes = $classQuery->get();

                if ($classes->isEmpty()) {
                    $errors[] = "Baris {$rowNumber}: kelas \"{$row['class']}\" tidak ditemukan. Impor kelas terlebih dahulu.";

                    continue;
                }

                if ($classes->count() > 1) {
                    $errors[] = "Baris {$rowNumber}: kelas \"{$row['class']}\" ada di beberapa tahun ajaran. Isi kolom Tahun Ajaran.";

                    continue;
                }

                $class = $classes->first();
                $row['class_id'] = (string) $class->id;
                $row['academic_year'] = $row['academic_year'] ?: $class->academic_year;
            }

            $preparedRows[] = $row;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => implode("\n", array_slice($errors, 0, 20))]);
        }

        return $preparedRows;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function persistRow(string $dataset, array $row): void
    {
        if ($dataset === 'students') {
            $this->createStudent($row);

            return;
        }

        if ($dataset === 'teachers') {
            $this->createTeacher($row);

            return;
        }

        if ($dataset === 'classes') {
            SchoolClass::updateOrCreate(
                ['name' => $row['name'], 'academic_year' => $row['academic_year']],
                ['grade' => $row['grade'], 'major' => $row['major'] ?? null, 'is_active' => true],
            );

            return;
        }

        DispensationType::firstOrCreate(
            ['name' => $row['name'], 'category' => $row['category']],
            ['description' => $row['description'] ?? null, 'is_active' => true],
        );
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function createStudent(array $row): void
    {
        $user = User::create([
            'name' => $row['name'],
            'username' => $row['username'],
            'email' => $row['email'] ?: null,
            'password' => $row['password'],
            'role' => User::ROLE_SISWA,
            'is_active' => true,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'nis' => $row['nis'],
            'nisn' => $row['nisn'] ?: null,
            'gender' => $row['gender'] ?: null,
            'phone' => $row['phone'] ?: null,
            'address' => $row['address'] ?: null,
            'is_active' => true,
        ]);

        if (isset($row['class_id'])) {
            StudentEnrollment::create([
                'student_id' => $student->id,
                'class_id' => $row['class_id'],
                'academic_year' => $row['academic_year'],
                'start_date' => now()->toDateString(),
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function createTeacher(array $row): void
    {
        $user = User::create([
            'name' => $row['name'],
            'username' => $row['username'],
            'email' => $row['email'] ?: null,
            'password' => $row['password'],
            'role' => User::ROLE_GURU,
            'is_active' => true,
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'nip' => $row['nip'] ?: null,
            'phone' => $row['phone'] ?: null,
            'position' => $row['position'] ?: null,
            'is_active' => true,
        ]);
    }

    private function normalizeHeader(string $header): string
    {
        return Str::of($header)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }

    /**
     * @return array<string, string>
     */
    private function uniqueFields(string $dataset): array
    {
        return match ($dataset) {
            'students' => ['username' => 'users', 'email' => 'users', 'nis' => 'students'],
            'teachers' => ['username' => 'users', 'email' => 'users', 'nip' => 'teachers'],
            default => [],
        };
    }
}
