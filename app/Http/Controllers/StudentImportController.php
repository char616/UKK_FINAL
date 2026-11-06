<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class StudentImportController extends Controller
{
    public const DEFAULT_IMPORT_PASSWORD = 'password123';

    private const REQUIRED_HEADERS = [
        'student_login_id',
        'student_name',
        'class_name',
        'major_code',
        'student_recovery_email',
        'parent_name',
        'parent_recovery_email',
    ];

    private const SAMPLE_ROWS = [
        [
            '2026100001',
            'Ahmad Fauzi',
            '10 RPL 1',
            'RPL',
            'ahmad.fauzi@contoh.sch.id',
            'Budi Santoso',
            'budi.santoso@contoh.sch.id',
        ],
        [
            '2026100002',
            'Siti Aisyah',
            '10 RPL 2',
            'RPL',
            'siti.aisyah@contoh.sch.id',
            'Hendra Wijaya',
            'hendra.wijaya@contoh.sch.id',
        ],
    ];

    public function downloadTemplate(Request $request): StreamedResponse
    {
        if (strtolower((string) $request->query('format')) === 'xlsx') {
            return response()->streamDownload(function (): void {
                $tempDir = storage_path('framework/temp');
                if (! is_dir($tempDir)) {
                    @mkdir($tempDir, 0777, true);
                }

                $xlsxPath = $tempDir.'/template-impor-siswa-scp-'.uniqid('', true).'.xlsx';
                self::writeXlsxFile($xlsxPath, array_merge([self::REQUIRED_HEADERS], self::SAMPLE_ROWS));
                echo (string) file_get_contents($xlsxPath);
                @unlink($xlsxPath);
            }, 'template-impor-siswa-scp.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, self::REQUIRED_HEADERS, ',', '"', '');
            foreach (self::SAMPLE_ROWS as $sampleRow) {
                fputcsv($output, $sampleRow, ',', '"', '');
            }
            fclose($output);
        }, 'template-impor-siswa-scp.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'roster' => ['nullable', 'file', 'max:5120'],
            'roster_data' => ['nullable', 'string'],
            'roster_name' => ['nullable', 'string', 'max:255'],
            'temporary_password' => ['nullable', 'string'],
        ]);

        [$headers, $dataRows] = $this->extractRowsFromRequest($request);

        foreach (self::REQUIRED_HEADERS as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                throw ValidationException::withMessages([
                    'roster' => 'Header file tidak sesuai template. Kolom wajib: '.implode(', ', self::REQUIRED_HEADERS),
                ]);
            }
        }

        $studentRole = Role::firstOrCreate(
            ['name' => 'siswa'],
            ['display_name' => 'Siswa']
        );
        $parentRole = Role::firstOrCreate(
            ['name' => 'orang_tua'],
            ['display_name' => 'Orang Tua']
        );

        $classes = Classes::with('major')->get();
        $parsedRows = [];

        foreach ($dataRows as $lineIndex => $values) {
            $isEmptyRow = true;
            foreach ($values as $cellValue) {
                if (trim((string) $cellValue) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) {
                continue;
            }

            $row = [];
            foreach ($headers as $colIndex => $headerName) {
                $row[$headerName] = isset($values[$colIndex]) ? trim((string) $values[$colIndex]) : '';
            }

            $rowNum = $lineIndex + 2;
            $studentLoginId = $row['student_login_id'] ?? '';
            $studentName = $row['student_name'] ?? '';
            $className = $row['class_name'] ?? '';
            $majorCode = $row['major_code'] ?? '';
            $studentRecoveryEmail = $row['student_recovery_email'] ?? '';
            $parentName = $row['parent_name'] ?? '';
            $parentRecoveryEmail = $row['parent_recovery_email'] ?? '';

            if (! preg_match('/^\d{10}$/', $studentLoginId)) {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: NISN (student_login_id) harus terdiri dari 10 digit angka.",
                ]);
            }

            if ($studentName === '' || $parentName === '') {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: Nama siswa dan nama orang tua wajib diisi.",
                ]);
            }

            $matchedClass = $classes->first(function (Classes $schoolClass) use ($className, $majorCode): bool {
                $schoolNameClean = Str::lower(trim($schoolClass->name));
                $inputNameClean = Str::lower(trim($className));

                $normalize = function (string $val): string {
                    $val = Str::replaceStart('kelas ', '', $val);
                    $val = preg_replace('/^10\b/', 'x', $val);
                    $val = preg_replace('/^11\b/', 'xi', $val);
                    $val = preg_replace('/^12\b/', 'xii', $val);

                    return Str::replace([' ', '_'], '', $val);
                };

                $sameName = ($schoolNameClean === $inputNameClean) || ($normalize($schoolNameClean) === $normalize($inputNameClean));

                return $sameName && Str::lower(trim((string) $schoolClass->major?->code)) === Str::lower($majorCode);
            });

            if (! $matchedClass) {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: Kelas '{$className}' dengan jurusan '{$majorCode}' tidak ditemukan pada Master Data.",
                ]);
            }

            if ($studentRecoveryEmail !== '' && ! filter_var($studentRecoveryEmail, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: Format email pemulihan siswa tidak valid.",
                ]);
            }

            if ($parentRecoveryEmail !== '' && ! filter_var($parentRecoveryEmail, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'roster' => "Baris {$rowNum}: Format email pemulihan orang tua tidak valid.",
                ]);
            }

            $parentLoginKey = $this->parentLoginKey($studentName);

            $parsedRows[] = [
                'row_num' => $rowNum,
                'student_login_id' => $studentLoginId,
                'student_name' => $studentName,
                'class_id' => $matchedClass->id,
                'student_recovery_email' => $studentRecoveryEmail !== '' ? $studentRecoveryEmail : null,
                'parent_login_key' => $parentLoginKey,
                'parent_name' => $parentName,
                'parent_recovery_email' => $parentRecoveryEmail !== '' ? $parentRecoveryEmail : null,
            ];
        }

        if ($parsedRows === []) {
            throw ValidationException::withMessages([
                'roster' => 'Tidak ada baris data siswa yang dapat diimpor.',
            ]);
        }

        $plainPassword = filled($validated['temporary_password'] ?? null)
            ? (string) $validated['temporary_password']
            : self::DEFAULT_IMPORT_PASSWORD;
        $hashedPassword = Hash::make($plainPassword);

        DB::transaction(function () use ($parsedRows, $studentRole, $parentRole, $hashedPassword): void {
            foreach ($parsedRows as $item) {
                $existingStudent = User::where('login_key', $item['student_login_id'])->lockForUpdate()->first();
                if ($existingStudent && (int) $existingStudent->role_id !== (int) $studentRole->id) {
                    throw ValidationException::withMessages([
                        'roster' => "Baris {$item['row_num']}: Login ID siswa '{$item['student_login_id']}' sudah digunakan oleh role lain.",
                    ]);
                }

                $existingParent = User::where('login_key', $item['parent_login_key'])->lockForUpdate()->first();
                if ($existingParent && (int) $existingParent->role_id !== (int) $parentRole->id) {
                    throw ValidationException::withMessages([
                        'roster' => "Baris {$item['row_num']}: Login ID orang tua '{$item['parent_login_key']}' sudah digunakan oleh role lain.",
                    ]);
                }

                if ($existingStudent) {
                    $studentAttrs = [
                        'name' => $item['student_name'],
                        'class_id' => $item['class_id'],
                        'is_active' => true,
                    ];
                    if ($item['student_recovery_email'] !== null) {
                        if (Str::lower((string) $existingStudent->recovery_email) !== Str::lower($item['student_recovery_email'])) {
                            $studentAttrs['recovery_email_verified_at'] = null;
                        }
                        $studentAttrs['recovery_email'] = $item['student_recovery_email'];
                    }
                    $existingStudent->update($studentAttrs);
                    $student = $existingStudent;
                } else {
                    $student = User::create([
                        'name' => $item['student_name'],
                        'login_key' => $item['student_login_id'],
                        'email' => $this->internalEmail($item['student_login_id']),
                        'recovery_email' => $item['student_recovery_email'],
                        'role_id' => $studentRole->id,
                        'class_id' => $item['class_id'],
                        'password' => $hashedPassword,
                        'must_change_password' => false,
                        'is_active' => true,
                    ]);
                }

                if ($existingParent) {
                    $parentAttrs = [
                        'name' => $item['parent_name'],
                        'is_active' => true,
                    ];
                    if ($item['parent_recovery_email'] !== null) {
                        if (Str::lower((string) $existingParent->recovery_email) !== Str::lower($item['parent_recovery_email'])) {
                            $parentAttrs['recovery_email_verified_at'] = null;
                        }
                        $parentAttrs['recovery_email'] = $item['parent_recovery_email'];
                    }
                    $existingParent->update($parentAttrs);
                    $parent = $existingParent;
                } else {
                    $parent = User::create([
                        'name' => $item['parent_name'],
                        'login_key' => $item['parent_login_key'],
                        'email' => $this->internalEmail($item['parent_login_key']),
                        'recovery_email' => $item['parent_recovery_email'],
                        'role_id' => $parentRole->id,
                        'password' => $hashedPassword,
                        'must_change_password' => false,
                        'is_active' => true,
                    ]);
                }

                $student->parents()->syncWithoutDetaching([$parent->id]);
            }

            AuditLog::record(
                Auth::id(),
                'IMPORT_STUDENTS',
                'WAKA mengimpor '.count($parsedRows).' data siswa beserta akun orang tua.'
            );
        });

        return redirect()->route('master-data.index')->with(
            'success',
            'Berhasil mengimpor '.count($parsedRows).' data siswa dan orang tua (Password default: '.self::DEFAULT_IMPORT_PASSWORD.').'
        );
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>}
     */
    private function extractRowsFromRequest(Request $request): array
    {
        $filePath = null;
        $originalName = '';
        $cleanupTempFile = false;

        if ($request->hasFile('roster') && $request->file('roster')?->isValid()) {
            $uploaded = $request->file('roster');
            $filePath = $uploaded->getRealPath();
            $originalName = (string) $uploaded->getClientOriginalName();
        } elseif (is_string($request->input('roster_data')) && trim($request->input('roster_data')) !== '') {
            $base64Data = $request->input('roster_data');
            if (! preg_match('/^data:[^;]+;base64,(.+)$/s', $base64Data, $matches)) {
                throw ValidationException::withMessages([
                    'roster' => 'Format file impor tidak valid.',
                ]);
            }

            $binary = base64_decode($matches[1], true);
            if ($binary === false || $binary === '') {
                throw ValidationException::withMessages([
                    'roster' => 'File impor tidak dapat dibaca.',
                ]);
            }

            $tempDir = storage_path('framework/temp');
            if (! is_dir($tempDir)) {
                @mkdir($tempDir, 0777, true);
            }

            $originalName = (string) $request->input('roster_name', 'roster.csv');
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'csv';
            $filePath = $tempDir.'/import_'.uniqid('', true).'.'.$ext;
            file_put_contents($filePath, $binary);
            $cleanupTempFile = true;
        } else {
            throw ValidationException::withMessages([
                'roster' => 'Silakan pilih file Excel (.xlsx) atau CSV (.csv) terlebih dahulu.',
            ]);
        }

        try {
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $firstBytes = (string) file_get_contents($filePath, false, null, 0, 4);
            $isZipXlsx = str_starts_with($firstBytes, "PK\x03\x04") || $extension === 'xlsx';

            $allRows = $isZipXlsx
                ? $this->parseXlsxRows($filePath)
                : $this->parseCsvRows((string) file_get_contents($filePath));
        } finally {
            if ($cleanupTempFile && $filePath && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        if (count($allRows) < 2) {
            throw ValidationException::withMessages([
                'roster' => 'File harus berisi baris header dan minimal 1 baris data siswa.',
            ]);
        }

        $headerRow = array_shift($allRows);
        $headers = array_map(
            fn ($value) => Str::lower(trim((string) $value)),
            $headerRow
        );

        return [$headers, array_values($allRows)];
    }

    /**
     * @return list<list<string>>
     */
    private function parseCsvRows(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split('/\r\n|\r|\n/', trim($content)) ?: [];

        if ($lines === [] || trim((string) ($lines[0] ?? '')) === '') {
            return [];
        }

        $headerLine = (string) $lines[0];
        $delimiter = substr_count($headerLine, ';') > substr_count($headerLine, ',') ? ';' : ',';

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $rows[] = array_map(
                fn ($v) => trim((string) $v),
                str_getcsv($line, $delimiter, '"', '')
            );
        }

        return $rows;
    }

    /**
     * @return list<list<string>>
     */
    private function parseXlsxRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw ValidationException::withMessages([
                'roster' => 'File Excel (.xlsx) tidak valid atau rusak.',
            ]);
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedStringsXml) && $sharedStringsXml !== '') {
            $sst = @simplexml_load_string($sharedStringsXml);
            if ($sst instanceof SimpleXMLElement) {
                foreach ($sst->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        $text = '';
                        foreach ($si->r as $run) {
                            $text .= (string) $run->t;
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (! is_string($sheetXml) || $sheetXml === '') {
            throw ValidationException::withMessages([
                'roster' => 'Lembar kerja utama (sheet1) pada file Excel tidak ditemukan.',
            ]);
        }

        $sheet = @simplexml_load_string($sheetXml);
        if (! $sheet instanceof SimpleXMLElement || ! isset($sheet->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $rowElement) {
            $rowCells = [];
            $maxColIndex = -1;

            foreach ($rowElement->c as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                $colLetters = preg_replace('/[^A-Z]/', '', strtoupper($ref)) ?: '';
                $colIndex = $colLetters !== '' ? $this->columnIndexFromLetters($colLetters) : ($maxColIndex + 1);
                $maxColIndex = max($maxColIndex, $colIndex);

                $type = (string) ($cell['t'] ?? '');
                $value = '';

                if ($type === 's') {
                    $idx = (int) ($cell->v ?? 0);
                    $value = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                } else {
                    $value = (string) ($cell->v ?? '');
                }

                $rowCells[$colIndex] = trim($value);
            }

            if ($maxColIndex >= 0) {
                $normalizedRow = [];
                for ($i = 0; $i <= $maxColIndex; $i++) {
                    $normalizedRow[] = $rowCells[$i] ?? '';
                }
                $rows[] = $normalizedRow;
            }
        }

        return $rows;
    }

    private function columnIndexFromLetters(string $letters): int
    {
        $index = 0;
        $length = strlen($letters);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    /**
     * @param list<list<string>> $rows
     */
    public static function writeXlsxFile(string $targetPath, array $rows): void
    {
        $zip = new ZipArchive();
        if ($zip->open($targetPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file template Excel.');
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Template Impor Siswa" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>');

        $sheetRowsXml = '';
        foreach ($rows as $rowIndex => $row) {
            $rNum = $rowIndex + 1;
            $cellsXml = '';
            foreach ($row as $colIndex => $cellValue) {
                $colLetter = chr(ord('A') + $colIndex);
                $cellRef = $colLetter.$rNum;
                $escaped = htmlspecialchars((string) $cellValue, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $cellsXml .= '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
            }
            $sheetRowsXml .= '<row r="'.$rNum.'">'.$cellsXml.'</row>';
        }

        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.$sheetRowsXml.'</sheetData>'
            .'</worksheet>');

        $zip->close();
    }

    private function parentLoginKey(string $studentName): string
    {
        $slug = Str::slug($studentName, '_');

        return ($slug !== '' ? $slug : 'siswa').'_ortu';
    }

    private function internalEmail(string $loginKey): string
    {
        return 'account-'.substr(hash('sha256', Str::lower(trim($loginKey))), 0, 32).'@accounts.invalid';
    }
}
