<?php

namespace Tests\Feature;

use App\Http\Controllers\StudentImportController;
use App\Models\Classes;
use App\Models\Major;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private int $userSequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['siswa', 'orang_tua'] as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['display_name' => str($roleName)->replace('_', ' ')->title()->toString()]
            );
        }
    }

    public function test_waka_can_import_many_students_with_one_parent_account_per_child_without_password_input(): void
    {
        $waka = $this->createUser('waka');
        $class = $this->createClass();
        $rows = [];

        for ($number = 1; $number <= 36; $number++) {
            $rows[] = [
                str_pad((string) (2026000000 + $number), 10, '0', STR_PAD_LEFT),
                'Siswa ' . $number,
                $class->name,
                $class->major->code,
                '',
                'Orang Tua ' . $number,
                '',
            ];
        }

        $this->actingAs($waka)->post(route('master-data.students.import'), [
            'roster' => $this->csvUpload($rows, ';'),
        ])->assertRedirect(route('master-data.index'))
            ->assertSessionHas('success');

        $students = User::whereIn('login_key', array_map(
            fn ($number) => str_pad((string) (2026000000 + $number), 10, '0', STR_PAD_LEFT),
            range(1, 36)
        ))->get();
        $parents = User::whereHas('role', fn ($query) => $query->where('name', 'orang_tua'))->get();

        $this->assertCount(36, $students);
        $this->assertCount(36, $parents);
        $this->assertSame(36, $parents->sum(fn (User $parent) => $parent->students()->count()));
        $this->assertSame('siswa_1_ortu', $parents->firstWhere('login_key', 'siswa_1_ortu')->login_key);
        $this->assertTrue($students->every(fn (User $student) => ! $student->must_change_password));
        $this->assertTrue($parents->every(fn (User $parent) => ! $parent->must_change_password));
        $this->assertSame('accounts.invalid', substr(strrchr($students->first()->email, '@'), 1));
        $this->assertTrue(Hash::check(StudentImportController::DEFAULT_IMPORT_PASSWORD, $students->first()->password));
        $this->assertDatabaseCount('parent_student', 36);
        $this->assertDatabaseHas('audit_logs', ['action' => 'IMPORT_STUDENTS']);
    }

    public function test_waka_can_import_students_from_xlsx_excel_file(): void
    {
        $waka = $this->createUser('waka');
        $class = $this->createClass();
        $rows = [
            [
                'student_login_id',
                'student_name',
                'class_name',
                'major_code',
                'student_recovery_email',
                'parent_name',
                'parent_recovery_email',
            ],
            [
                '2026990001',
                'Siswa Excel Satu',
                $class->name,
                $class->major->code,
                'siswa1@contoh.sch.id',
                'Orang Tua Excel Satu',
                'ortu1@contoh.sch.id',
            ],
        ];

        $tempPath = storage_path('framework/temp/test-import-' . uniqid('', true) . '.xlsx');
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }
        StudentImportController::writeXlsxFile($tempPath, $rows);
        $xlsxContent = (string) file_get_contents($tempPath);
        @unlink($tempPath);

        $upload = UploadedFile::fake()->createWithContent('roster.xlsx', $xlsxContent);

        $this->actingAs($waka)->post(route('master-data.students.import'), [
            'roster' => $upload,
        ])->assertRedirect(route('master-data.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'login_key' => '2026990001',
            'name' => 'Siswa Excel Satu',
            'must_change_password' => false,
        ]);
        $this->assertDatabaseHas('users', [
            'login_key' => 'siswa_excel_satu_ortu',
            'name' => 'Orang Tua Excel Satu',
            'must_change_password' => false,
        ]);
    }

    public function test_reimport_reuses_existing_student_and_parent_without_resetting_password(): void
    {
        $waka = $this->createUser('waka');
        $class = $this->createClass();
        $rows = [[
            '1234567890',
            'Siswa Pertama',
            $class->name,
            $class->major->code,
            '',
            'Orang Tua',
            '',
        ]];

        $this->actingAs($waka)->post(route('master-data.students.import'), [
            'roster' => $this->csvUpload($rows),
        ])->assertRedirect(route('master-data.index'));

        $student = User::where('login_key', '1234567890')->firstOrFail();
        $originalPassword = $student->password;

        $this->actingAs($waka)->post(route('master-data.students.import'), [
            'roster' => $this->csvUpload($rows),
        ])->assertRedirect(route('master-data.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('parent_student', 1);
        $this->assertSame($originalPassword, $student->fresh()->password);
        $this->assertSame('Siswa Pertama', $student->fresh()->name);
    }

    public function test_invalid_roster_does_not_create_any_accounts(): void
    {
        $waka = $this->createUser('waka');
        $class = $this->createClass();
        $rows = [
            ['1234567801', 'Siswa Valid', $class->name, $class->major->code, '', 'Orang Tua Valid', ''],
            ['1234567802', 'Siswa Invalid', 'Kelas Tidak Ada', $class->major->code, '', 'Orang Tua Invalid', ''],
        ];

        $this->actingAs($waka)->from(route('master-data.index'))->post(route('master-data.students.import'), [
            'roster' => $this->csvUpload($rows),
        ])->assertRedirect(route('master-data.index'))
            ->assertSessionHasErrors('roster');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('parent_student', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_import_rejects_a_parent_login_id_owned_by_another_role_without_saving(): void
    {
        $waka = $this->createUser('waka');
        $teacher = $this->createUser('guru');
        $teacher->update(['login_key' => 'siswa_ortu']);
        $class = $this->createClass();

        $this->actingAs($waka)->from(route('master-data.index'))->post(route('master-data.students.import'), [
            'roster' => $this->csvUpload([[
                '1234567803',
                'Siswa',
                $class->name,
                $class->major->code,
                '',
                'Orang Tua',
                '',
            ]]),
        ])->assertRedirect(route('master-data.index'))
            ->assertSessionHasErrors('roster');

        $this->assertDatabaseMissing('users', ['login_key' => '1234567803']);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_non_waka_cannot_download_or_import_roster(): void
    {
        $student = $this->createUser('siswa');

        $this->actingAs($student)
            ->get(route('master-data.students.import-template'))
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('master-data.students.import'))
            ->assertForbidden();
    }

    public function test_template_download_contains_the_required_csv_and_xlsx_headers(): void
    {
        $waka = $this->createUser('waka');

        $response = $this->actingAs($waka)->get(route('master-data.students.import-template'));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'student_login_id,student_name,class_name,major_code',
            $response->streamedContent()
        );

        $xlsxResponse = $this->actingAs($waka)->get(route('master-data.students.import-template', ['format' => 'xlsx']));
        $xlsxResponse->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function createUser(string $roleName): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => str($roleName)->replace('_', ' ')->title()->toString()]
        );
        $sequence = ++$this->userSequence;

        return User::create([
            'name' => 'User ' . $sequence,
            'email' => 'import-user-' . $sequence . '@school.test',
            'login_key' => 'IMPORT-USER-' . $sequence,
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function createClass(): Classes
    {
        $major = Major::create(['code' => 'PPLG', 'name' => 'Pengembangan Perangkat Lunak dan Gim']);

        return Classes::create([
            'name' => 'X PPLG 1',
            'major_id' => $major->id,
            'phase' => 'ganjil',
        ])->load('major');
    }

    private function csvUpload(array $rows, string $delimiter = ','): UploadedFile
    {
        $stream = fopen('php://temp', 'r+');
        $headers = [
            'student_login_id',
            'student_name',
            'class_name',
            'major_code',
            'student_recovery_email',
            'parent_name',
            'parent_recovery_email',
        ];

        fputcsv($stream, $headers, $delimiter, '"', '');
        foreach ($rows as $row) {
            fputcsv($stream, $row, $delimiter, '"', '');
        }
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return UploadedFile::fake()->createWithContent('roster.csv', $contents);
    }
}
