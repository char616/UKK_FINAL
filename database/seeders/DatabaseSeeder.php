<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Major;
use App\Models\Role;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            // 1. Role Sistem (Admin/Waka, BK, Wali Kelas, Guru, Siswa, Orang Tua)
            $roles = [
                ['name' => 'waka', 'display_name' => 'Admin / Waka'],
                ['name' => 'bk', 'display_name' => 'Guru BK'],
                ['name' => 'wali_kelas', 'display_name' => 'Wali Kelas'],
                ['name' => 'guru', 'display_name' => 'Guru'],
                ['name' => 'siswa', 'display_name' => 'Siswa'],
                ['name' => 'orang_tua', 'display_name' => 'Orang Tua'],
            ];

            foreach ($roles as $roleData) {
                Role::query()->updateOrCreate(['name' => $roleData['name']], $roleData);
            }

            $studentRole = Role::query()->where('name', 'siswa')->firstOrFail();
            $parentRole = Role::query()->where('name', 'orang_tua')->firstOrFail();

            $wakaRole = Role::query()->where('name', 'waka')->firstOrFail();
            $bkRole = Role::query()->where('name', 'bk')->firstOrFail();
            $homeroomRole = Role::query()->where('name', 'wali_kelas')->firstOrFail();
            $teacherRole = Role::query()->where('name', 'guru')->firstOrFail();

            DB::table('users')->update(['is_active' => true]);

            if (app()->environment('testing')) {
                $this->seedTestingData($wakaRole, $bkRole, $homeroomRole, $teacherRole, $studentRole);
                return;
            }

            // Pastikan akun Admin / Waka utama tetap tersedia
            $this->upsertAccount(
                loginKey: 'WAKA001',
                name: 'Sunarsih, S.Pd.',
                email: 'waka@school.test',
                role: $wakaRole
            );
            User::query()->where('role_id', $wakaRole->id)->update(['name' => 'Sunarsih, S.Pd.']);

            // Bersihkan seluruh akun dummy lama (Guru dummy, Wali dummy, BK dummy, Siswa/Ortu dummy)
            DB::table('parent_student')->delete();
            Classes::query()->update(['homeroom_teacher_id' => null]);

            User::query()
                ->where('login_key', '!=', 'WAKA001')
                ->where(function ($query) use ($teacherRole, $homeroomRole, $studentRole, $parentRole, $bkRole): void {
                    $query->whereIn('role_id', [$teacherRole->id, $homeroomRole->id, $studentRole->id, $parentRole->id, $bkRole->id])
                        ->orWhereIn('login_key', [
                            'BK001', 'WALI001', 'GURU001', 'SISWA001', 'ORTU001',
                            'BK_1', 'BK_2', 'BK_3', 'BK_4', 'BK_5', 'BK_6',
                            'GURU_1', 'GURU_2', 'GURU_3', 'GURU_4', 'GURU_5',
                        ])
                        ->orWhere('login_key', 'like', 'BK_%')
                        ->orWhere('login_key', 'like', 'WALI_%')
                        ->orWhere('login_key', 'like', 'GURU_%');
                })
                ->delete();

            // 2. DATA BK (Tepat 6 Akun: BK_1 s/d BK_6)
            for ($i = 1; $i <= 6; $i++) {
                $this->upsertAccount(
                    loginKey: "BK_{$i}",
                    name: "Guru BK {$i}",
                    email: "bk_{$i}@school.test",
                    role: $bkRole
                );
            }

            // 3. DATA JURUSAN (Majors)
            $majorsData = [
                'RPL' => 'Rekayasa Perangkat Lunak',
                'TKJ' => 'Teknik Komputer dan Jaringan',
                'DKV' => 'Desain Komunikasi Visual',
                'AK'  => 'Akuntansi',
                'LPS' => 'Layanan Perbankan Syariah',
                'MP'  => 'Manajemen Perkantoran',
                'BR'  => 'Bisnis Retail',
                'BD'  => 'Bisnis Digital',
            ];

            $majors = [];
            foreach ($majorsData as $code => $name) {
                $majors[$code] = Major::query()->updateOrCreate(
                    ['code' => $code],
                    ['name' => $name]
                );
            }

            // 4. DATA KELAS (Total 49 Kelas) & AKUN WALI KELAS (Menggunakan Nama Kelasnya)
            $allClassNames = [
                // Kelas X (16 Kelas)
                'X RPL 1', 'X RPL 2',
                'X TKJ 1', 'X TKJ 2',
                'X DKV 1', 'X DKV 2',
                'X AK 1', 'X AK 2', 'X AK 3',
                'X LPS 1', 'X LPS 2',
                'X MP 1', 'X MP 2',
                'X BR 1', 'X BR 2',
                'X BD',

                // Kelas XI (16 Kelas)
                'XI RPL 1', 'XI RPL 2',
                'XI TKJ 1', 'XI TKJ 2',
                'XI DKV 1', 'XI DKV 2',
                'XI AK 1', 'XI AK 2', 'XI AK 3',
                'XI LPS 1', 'XI LPS 2',
                'XI MP 1', 'XI MP 2',
                'XI BR 1', 'XI BR 2',
                'XI BD',

                // Kelas XII (17 Kelas)
                'XII RPL 1', 'XII RPL 2',
                'XII TKJ 1', 'XII TKJ 2',
                'XII DKV 1', 'XII DKV 2',
                'XII AK 1', 'XII AK 2', 'XII AK 3', 'XII AK 4',
                'XII LPS',
                'XII MP 1', 'XII MP 2',
                'XII BR 1', 'XII BR 2',
                'XII BD',
            ];

            foreach ($allClassNames as $className) {
                $majorCode = $this->resolveMajorCodeFromClassName($className);
                $phase = $this->resolvePhaseFromClassName($className);

                // Migrasi nama kelas format lama ("Kelas X RPL1" / "Kelas X RPL 1") ke format baru ("X RPL 1")
                $legacyName = 'Kelas ' . preg_replace('/\s+(\d+)$/', '$1', $className);
                $altLegacyName = 'Kelas ' . $className;

                $schoolClass = Classes::query()
                    ->where('name', $className)
                    ->orWhere('name', $legacyName)
                    ->orWhere('name', $altLegacyName)
                    ->first();

                if ($schoolClass) {
                    $schoolClass->update([
                        'name' => $className,
                        'major_id' => $majors[$majorCode]->id,
                        'phase' => $phase,
                    ]);
                } else {
                    $schoolClass = Classes::query()->create([
                        'name' => $className,
                        'major_id' => $majors[$majorCode]->id,
                        'phase' => $phase,
                    ]);
                }

                // Buat Akun Wali Kelas menggunakan nama kelasnya (contoh: "X RPL 1" dst)
                $waliEmail = Str::slug($className, '_') . '@school.test';
                $wali = $this->upsertAccount(
                    loginKey: $className,
                    name: $className,
                    email: $waliEmail,
                    role: $homeroomRole
                );

                // Tetapkan sebagai wali kelas untuk kelas ini
                $schoolClass->update(['homeroom_teacher_id' => $wali->id]);
            }

            // Hapus kelas dummy lama yang bukan bagian dari 49 kelas riil
            Classes::query()->whereNotIn('name', $allClassNames)->delete();

            // Hapus jurusan dummy lama yang sudah tidak memiliki kelas
            Major::query()->whereNotIn('code', array_keys($majorsData))->whereDoesntHave('classes')->delete();

            $kelasXiiRpl1 = Classes::query()->where('name', 'XII RPL 1')->firstOrFail();

            // 4. DATA GURU (86 Guru - NIP Sementara diisi angka urut "1" s/d "86")
            $teachersData = [
                1 => 'Agatha Christiyanti Manullang, S.S',
                2 => 'Agnes Endang Mudji Rahayu, M.Pd.',
                3 => 'Ahmad Muhlasin, S.Pd.',
                4 => 'Aisyah Wulandari, S.Pd',
                5 => 'Ajeng Fitri Saraswati, S.Pd',
                6 => 'Alivi Sazkia Hawie, S.Pd',
                7 => 'Amat Sukardi, S.Pd',
                8 => 'Anastya Ratna Sulistiyani, S.Pd',
                9 => 'Andriyani, S.Kom',
                10 => 'Aris Purwantinah, S.Pd.',
                11 => 'Budiyantoro, S.Pd.',
                12 => 'Candra Rahadian, S.Pd',
                13 => 'Danurejo, S.Pd',
                14 => 'Dariyati, S.Kom.',
                15 => 'Diah Utaminingsih, S.T',
                16 => 'Dian Awaliati, S.Pd.',
                17 => 'Diana Rohmaniah, S.Kom',
                18 => 'Dinda Ayu Dewanti, S.Kom',
                19 => 'Dita Rizki Dewinta Sari, S.Pd',
                20 => 'Dra. Eko Mei Wulan',
                21 => 'Dra. Mukirah, SPd.',
                22 => 'Dra. Siti Khusnul Khotimah.',
                23 => 'Dra. Yatimatin Nafiah, MM.',
                24 => 'Drs. Susanto Dwi Prihantoro, M.Pd',
                25 => 'Duwi Rianto, S.Pd',
                26 => 'Dwi Lesmiyati, S.Pd.',
                27 => 'Eneng Fitriani Puspitowati, S.Pd.Si',
                28 => 'Eridha Dewi Sepitasari, S.Pd.',
                29 => 'Eva Yuni Astuti, S.Pd.',
                30 => 'Failla Rochmayanti, M.Pd.',
                31 => 'Fiani Nur Aji Pangastuti, S.Pd',
                32 => 'Haris Budiawan, S.Pd',
                33 => 'Hermi Cahyawati, S.Pd.I',
                34 => 'Isti Fitriana, S.Pd',
                35 => 'Junia Sari, S.Pd',
                36 => 'Kurnia Safitri, S.Pd.',
                37 => 'Lauhin Ilmi Agustin, S.Pd',
                38 => 'Margiyati, S.Pd',
                39 => 'Menik Lestari, S.Pd',
                40 => 'Muhammad Furqon, S.Sos',
                41 => 'Muhammad Heru Purnomo, M.S.I',
                42 => 'Mulati, S.Pd.',
                43 => 'Murdiasih Kadarwati, S.Pd',
                44 => 'Ninda Aprilia, S.Pd',
                45 => 'Novia Rahmalina, S.Pd',
                46 => 'Nurhidayat Sulistiawan, S.Pd',
                47 => 'Nurzanah, S.Pd.',
                48 => 'Putri Wulanjari, S.Pd',
                49 => 'Raden Rara Bernadeta Sri Widyastuti, S.Pd',
                50 => 'Raden Rara Uningsuciasih, S.Pd',
                51 => 'Rahmawati Yoga Pamungkas, M.Pd',
                52 => 'Reny Murni Hidayati, S.Pd',
                53 => 'Retnaningsih, S.Pd.',
                54 => 'Rini Bhintarti, S.Pd.',
                55 => 'Ririh Damayanti Wisnu Giyono, M.Acc.',
                56 => 'Risa Resti Afriani, S.Pd',
                57 => 'Ristiyanti, SPd',
                58 => 'Rohmad Dwiyanto, S.Pd',
                59 => 'Rosyida Ramadhani, S.Pd',
                60 => 'Rusdiyanto, S.Pd',
                61 => 'Ryawidyaningtyas, S.Pd',
                62 => 'Sellyana Nurul Azizah, S.Pd',
                63 => 'Sri Nur Arafah, S.Pd.',
                64 => 'Sri Sunarsih, S.Pd.',
                65 => 'Sri Sunartini, S.Pd.',
                66 => 'Sujar Hartono, S.Pd',
                67 => 'Sulardi, S.Pd.',
                68 => 'Sunarsih, S.Pd',
                69 => 'Suratimah, S.Pd.I',
                70 => 'Sutarmi, S.Pd.',
                71 => 'Tantri Agustiana, S.Pd.',
                72 => 'Tatik Herawati, S.Pd',
                73 => 'Tatik Wardayati, S.Pd.',
                74 => 'Tentrem Yuniati Lestari, S.Pd',
                75 => 'Theresia Indri Wahyuni, S.P.d',
                76 => 'Tri Susilowati, S.T., M.Pd',
                77 => 'Umi Kuntari, S.S',
                78 => 'Wahyuni, S.Pd',
                79 => 'Wakhid Bashori, S.Pd',
                80 => 'Wati, S.Pd',
                81 => 'Widodo, S.Pd.',
                82 => 'Windu Mahmud, M.Eng',
                83 => 'Yanu Iswantara, S.Pd',
                84 => 'Yuli Arbiatiningsih, S.Pd',
                85 => 'Yuli Astuti, S.Pd.',
                86 => 'Yulita Eka Winajeng, S.Pd',
            ];

            $defaultPassword = Hash::make('password123');

            foreach ($teachersData as $nipNumber => $teacherName) {
                $nip = (string) $nipNumber;

                User::query()->updateOrCreate(
                    ['login_key' => $nip],
                    [
                        'name' => $teacherName,
                        'nip' => $nip,
                        'nis' => null,
                        'parent_name' => null,
                        'parent_login_key' => null,
                        'email' => "guru{$nip}@school.test",
                        'role_id' => $teacherRole->id,
                        'class_id' => null,
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'is_active' => true,
                    ]
                );
            }

            // 5. DATA SISWA & ORTU (Masukkan ke Kelas XII RPL1)
            // Siswa login pakai NIS, Ortu login pakai format `Nama Siswa_Nama Ortu` (case-insensitive)
            $studentsData = [
                ['nis' => '17756', 'name' => 'Abid Muhammad Rasyid', 'parent_name' => 'Sumarjono'],
                ['nis' => '17757', 'name' => 'Alfian Yofa Setya Budi', 'parent_name' => 'Wiyono'],
                ['nis' => '17758', 'name' => 'ALIFA FATIHATURROHMAH', 'parent_name' => 'AHMAD SARI'],
                ['nis' => '17759', 'name' => 'AMMAR FATHURRAHMAN', 'parent_name' => 'DWI SUMANTORO'],
                ['nis' => '17760', 'name' => 'ARSYA MIFTA ARDHIFA', 'parent_name' => 'SUGENG DWIANTO'],
                ['nis' => '17761', 'name' => 'DAFFA ATHA MUSYAFFA', 'parent_name' => 'HERI DWI PURWANTO'],
                ['nis' => '17762', 'name' => 'DESWINTA CAYLA RAHMA', 'parent_name' => 'WAHYU WINARNO'],
                ['nis' => '17763', 'name' => 'DEWI HANDAYANI NUR HALIMAH', 'parent_name' => 'SUGENG RAHARJO'],
                ['nis' => '17764', 'name' => 'FABIAN IRSYAD PRATAMA', 'parent_name' => 'FERY'],
                ['nis' => '17765', 'name' => 'FAHRIZA AHMAD', 'parent_name' => 'WAHADI'],
                ['nis' => '17766', 'name' => 'Fikri Nur Cavan Rijal', 'parent_name' => 'Cahyo Wantoro'],
                ['nis' => '17767', 'name' => 'Ghulam Iqbal Arrauf', 'parent_name' => 'Budi Santosa'],
                ['nis' => '17768', 'name' => 'Imam Budiyono', 'parent_name' => 'Aris Budiyono'],
                ['nis' => '17769', 'name' => 'JERICHO DEVON PUTRA SANTOSO', 'parent_name' => 'ANDY SANTOSO'],
                ['nis' => '17770', 'name' => 'Luhitha Septia Ramadhani', 'parent_name' => 'Suharyadi'],
                ['nis' => '17771', 'name' => 'Maudya Fathiya Azzahra', 'parent_name' => 'Riswanto'],
                ['nis' => '17772', 'name' => 'MAULANI CHARLIE GUSTAVI', 'parent_name' => 'TRI WINARTO'],
                ['nis' => '17773', 'name' => 'Miftahul Ramadhan', 'parent_name' => 'Prima Riyanto'],
                ['nis' => '17774', 'name' => 'MUHAMMAD ADIB FATHURROHMAN', 'parent_name' => 'MUHAMMAD NASIR'],
                ['nis' => '17775', 'name' => 'MUHAMMAD ADITYA CHANDRA', 'parent_name' => 'SUDARSANA'],
                ['nis' => '17776', 'name' => 'Muhammad Akmal Farandi', 'parent_name' => 'Muhammad Sholikhin'],
                ['nis' => '17777', 'name' => 'MUHAMMAD AZZAM NUR ROSYID PUTRA LAKSON', 'parent_name' => 'LAKSONO AGUS MARGONO'],
                ['nis' => '17778', 'name' => 'Muhammad Bramasena Prasetyo', 'parent_name' => 'Sigit Karsono'],
                ['nis' => '17779', 'name' => 'Muhammad Rafi Alhabib', 'parent_name' => 'Mulyani'],
                ['nis' => '17780', 'name' => 'MUHAMMAD RAFIUDDIN AKRAM', 'parent_name' => 'AGUS ROCHMADI'],
                ['nis' => '17781', 'name' => 'MUHAMMAD WILDAN FAQIH S', 'parent_name' => 'SISWANTO'],
                ['nis' => '17782', 'name' => 'MUHAMMAD ZAWAATUL AFNAAN', 'parent_name' => 'MUJIYONO'],
                ['nis' => '17783', 'name' => 'NADHIF ZHAVA ALHAVIS', 'parent_name' => 'RIYANTO BROTO'],
                ['nis' => '17784', 'name' => 'Rafasha Fawwaz Hakim', 'parent_name' => 'Hariyadi'],
                ['nis' => '17785', 'name' => 'Raihan Ankaa Fadil Alfatih', 'parent_name' => 'Andi Pramono'],
                ['nis' => '17786', 'name' => 'Reno Cahya Hutama', 'parent_name' => 'Eka Wantara'],
                ['nis' => '17788', 'name' => 'RIZKI JUNIARTANTO', 'parent_name' => 'GUNAWAN WIB'],
                ['nis' => '17789', 'name' => 'RIZKY ARDIANSYAH', 'parent_name' => 'MUKIJAN'],
                ['nis' => '17790', 'name' => 'Satria Yudhatama', 'parent_name' => 'Hari Purwanto'],
                ['nis' => '17791', 'name' => 'SYIFA EVELYNA KHANSA', 'parent_name' => 'DANANG PURWANTO'],
            ];

            foreach ($studentsData as $studentItem) {
                $nis = $studentItem['nis'];
                $studentName = trim($studentItem['name']);
                $parentName = trim($studentItem['parent_name']);

                // Format login Ortu: nama_siswa_ortu serta mendukung format Nama Siswa_Nama Ortu
                $parentLoginKey = Str::lower($studentName . '_' . $parentName);
                $parentAccountLoginKey = Str::slug($studentName, '_') . '_ortu';

                $student = User::query()->updateOrCreate(
                    ['login_key' => $nis],
                    [
                        'name' => $studentName,
                        'nip' => null,
                        'nis' => $nis,
                        'parent_name' => $parentName,
                        'parent_login_key' => $parentLoginKey,
                        'email' => "siswa{$nis}@school.test",
                        'role_id' => $studentRole->id,
                        'class_id' => $kelasXiiRpl1->id,
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'is_active' => true,
                    ]
                );

                $parent = User::query()->updateOrCreate(
                    ['login_key' => $parentAccountLoginKey],
                    [
                        'name' => $parentName,
                        'nip' => null,
                        'nis' => null,
                        'parent_name' => $parentName,
                        'parent_login_key' => $parentLoginKey,
                        'email' => "ortu{$nis}@school.test",
                        'role_id' => $parentRole->id,
                        'class_id' => $kelasXiiRpl1->id,
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'is_active' => true,
                    ]
                );

                $student->parents()->syncWithoutDetaching([$parent->id]);
            }
        });

        if (app()->environment('testing')) {
            return;
        }

        // 6. Seed Jam Pelajaran / Sesi
        $this->call(JamPelajaranSeeder::class);

        // 7. Seed Master Mata Pelajaran (69 Mapel Resmi)
        $officialSubjects = $this->getOfficialSubjects();
        $codes = array_column($officialSubjects, 'code');

        foreach ($officialSubjects as $subject) {
            Subject::query()->updateOrCreate(
                ['code' => $subject['code']],
                ['name' => $subject['name']]
            );
        }

        // Hapus mapel dummy lama yang tidak termasuk dalam daftar 69 mapel
        Subject::query()->whereNotIn('code', $codes)->delete();

        // 8. Seed Master Ruangan (60 Ruangan Resmi)
        self::ensureOfficialRoomsSeeded();
    }

    public static function ensureOfficialRoomsSeeded(): void
    {
        $officialRooms = self::getOfficialRooms();
        $roomCodes = array_column($officialRooms, 'code');

        Room::query()->whereNotIn('code', $roomCodes)->whereDoesntHave('schedules')->delete();

        foreach ($officialRooms as $roomData) {
            Room::query()->updateOrCreate(
                ['code' => $roomData['code']],
                [
                    'name' => $roomData['name'],
                    'capacity' => $roomData['capacity'] ?? 36,
                    'rules' => $roomData['rules'] ?? null,
                ]
            );
        }
    }

    private function seedTestingData(Role $wakaRole, Role $bkRole, Role $homeroomRole, Role $teacherRole, Role $studentRole): void
    {
        $major = Major::query()->updateOrCreate(['code' => 'RPL'], ['name' => 'Rekayasa Perangkat Lunak']);

        // 1 Waka
        $this->upsertAccount('WAKA001', 'Sunarsih, S.Pd.', 'waka@school.test', $wakaRole);

        // 6 BK: BK_1 s/d BK_6
        for ($i = 1; $i <= 6; $i++) {
            $this->upsertAccount("BK_{$i}", "Guru BK {$i}", "bk_{$i}@school.test", $bkRole);
        }

        // 6 Wali Kelas: WALI_10_RPL_1, WALI_10_RPL_2, WALI_11_RPL_1, WALI_11_RPL_2, WALI_12_RPL_1, WALI_12_RPL_2
        $classWaliMap = [
            '10 RPL 1' => 'WALI_10_RPL_1',
            '10 RPL 2' => 'WALI_10_RPL_2',
            '11 RPL 1' => 'WALI_11_RPL_1',
            '11 RPL 2' => 'WALI_11_RPL_2',
            '12 RPL 1' => 'WALI_12_RPL_1',
            '12 RPL 2' => 'WALI_12_RPL_2',
        ];

        foreach ($classWaliMap as $className => $waliLoginKey) {
            $wali = $this->upsertAccount($waliLoginKey, "Wali {$className}", "{$waliLoginKey}@school.test", $homeroomRole);
            Classes::query()->updateOrCreate(
                ['name' => $className],
                [
                    'major_id' => $major->id,
                    'phase' => str_contains($className, '2') ? 'genap' : 'ganjil',
                    'homeroom_teacher_id' => $wali->id,
                ]
            );
        }

        // 5 Guru: GURU_1 s/d GURU_5
        for ($i = 1; $i <= 5; $i++) {
            $teacher = $this->upsertAccount("GURU_{$i}", "Guru Pengajar {$i}", "guru_{$i}@school.test", $teacherRole);
            $teacher->update(['nip' => (string) $i]);
        }

        // 6 Rooms: RUANG-1 to RUANG-3, LAB-RPL-1 to LAB-RPL-3
        $rooms = [
            ['code' => 'RUANG-1', 'name' => 'Ruang 1', 'capacity' => 36],
            ['code' => 'RUANG-2', 'name' => 'Ruang 2', 'capacity' => 36],
            ['code' => 'RUANG-3', 'name' => 'Ruang 3', 'capacity' => 36],
            ['code' => 'LAB-RPL-1', 'name' => 'Lab RPL 1', 'capacity' => 36],
            ['code' => 'LAB-RPL-2', 'name' => 'Lab RPL 2', 'capacity' => 36],
            ['code' => 'LAB-RPL-3', 'name' => 'Lab RPL 3', 'capacity' => 36],
        ];
        foreach ($rooms as $room) {
            Room::query()->updateOrCreate(['code' => $room['code']], $room);
        }

        // 3 Subjects
        $subjects = [
            ['code' => 'PBO', 'name' => 'Pemrograman Berorientasi Objek'],
            ['code' => 'PWPB', 'name' => 'Pemrograman Web dan Perangkat Bergerak'],
            ['code' => 'BD', 'name' => 'Basis Data'],
        ];
        foreach ($subjects as $subject) {
            Subject::query()->updateOrCreate(['code' => $subject['code']], $subject);
        }
    }

    private function resolveMajorCodeFromClassName(string $className): string
    {
        // Contoh format: "X RPL 1", "Kelas X RPL1", "Kelas XII LPS", "XII BD"
        $parts = preg_split('/\s+/', trim($className)) ?: [];
        if (isset($parts[0]) && strtolower($parts[0]) === 'kelas') {
            array_shift($parts);
        }
        $majorToken = $parts[1] ?? ($parts[0] ?? 'RPL');
        $majorCode = preg_replace('/\d+$/', '', strtoupper($majorToken)) ?: 'RPL';

        return $majorCode;
    }

    private function resolvePhaseFromClassName(string $className): string
    {
        if (preg_match('/(\d+)$/', trim($className), $matches)) {
            $number = (int) $matches[1];

            return ($number % 2 === 0) ? 'genap' : 'ganjil';
        }

        return 'ganjil';
    }

    private function upsertAccount(string $loginKey, string $name, string $email, Role $role): User
    {
        // Hindari bentrok email unik jika ada akun sebelumnya yang berbeda login_key
        User::query()->where('email', $email)->where('login_key', '!=', $loginKey)->delete();

        $account = User::query()->firstOrNew(['login_key' => $loginKey]);
        $isNewAccount = ! $account->exists;

        $account->fill([
            'name' => $name,
            'email' => $email,
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        if ($isNewAccount) {
            $account->password = Hash::make('password123');
            $account->must_change_password = false;
        }

        $account->save();

        return $account;
    }

    public static function getOfficialSubjects(): array
    {
        return [
            ['code' => '3D', 'name' => '3D'],
            ['code' => 'ASJ', 'name' => 'Administrasi Sistem Jaringan'],
            ['code' => 'ADM-UMUM', 'name' => 'Administrasi Umum'],
            ['code' => 'AK-KEU', 'name' => 'Akuntansi Keuangan'],
            ['code' => 'AK-LIP', 'name' => 'Akuntansi Lembaga/Instansi Pemerintah'],
            ['code' => 'AK-PBS', 'name' => 'Akuntansi Perbankan Syariah'],
            ['code' => 'APS', 'name' => 'Analisa dan Perancangan Sistem'],
            ['code' => 'BINDO', 'name' => 'Bahasa Indonesia'],
            ['code' => 'BING', 'name' => 'Bahasa Inggris'],
            ['code' => 'BJAWA', 'name' => 'Bahasa Jawa'],
            ['code' => 'BD', 'name' => 'Basis Data'],
            ['code' => 'BDML', 'name' => 'Bigdata & Machine Learning'],
            ['code' => 'BK', 'name' => 'Bimbingan dan Konseling'],
            ['code' => 'BONLINE', 'name' => 'Bisnis Online'],
            ['code' => 'BRETAIL', 'name' => 'Bisnis Retail'],
            ['code' => 'CYBER', 'name' => 'Cyber Security'],
            ['code' => 'DAP', 'name' => 'Dasar Algoritma Pemrograman'],
            ['code' => 'DPBK', 'name' => 'Dasar Profesi dan Budaya Kerja'],
            ['code' => 'DPK-AK', 'name' => 'Dasar Program Keahlian Akuntansi'],
            ['code' => 'DPK-MP', 'name' => 'Dasar Program Keahlian Manajemen Perkantoran'],
            ['code' => 'DPK-PM', 'name' => 'Dasar Program Keahlian Pemasaran'],
            ['code' => 'DWJK', 'name' => 'Dasar Web dan Jaringan Komputer'],
            ['code' => 'DIGIMAR', 'name' => 'Digital Marketing'],
            ['code' => 'DOB', 'name' => 'Digital On Boarding'],
            ['code' => 'EKIS', 'name' => 'Ekonomi Islam'],
            ['code' => 'FOTO', 'name' => 'Fotografi'],
            ['code' => 'GAMEART', 'name' => 'Game Art'],
            ['code' => 'GDT', 'name' => 'Graphic Design Technology'],
            ['code' => 'INFOR', 'name' => 'Informatika'],
            ['code' => 'IOT', 'name' => 'Internet Of Thinks'],
            ['code' => 'K3LH', 'name' => 'K3LH & Budaya Kerja'],
            ['code' => 'UIUX', 'name' => 'Karya UI/UX'],
            ['code' => 'KAMJAR', 'name' => 'Keamanan Jaringan'],
            ['code' => 'ARSIP', 'name' => 'Kearsipan'],
            ['code' => 'KEU', 'name' => 'Keuangan'],
            ['code' => 'KOMPAK', 'name' => 'Komputer Akuntansi'],
            ['code' => 'KOMUN', 'name' => 'Komunikasi'],
            ['code' => 'KORES-MH', 'name' => 'Korespondensi & MH'],
            ['code' => 'KIK', 'name' => 'Kreatifitas, Inovasi dan kewirausahaan'],
            ['code' => 'LLKS', 'name' => 'Layanan Lembaga Keuangan Syariah'],
            ['code' => 'LOGIS', 'name' => 'Logistik'],
            ['code' => 'MTK', 'name' => 'Matematika'],
            ['code' => 'MJT', 'name' => 'Media & Jaringan Telekomunikasi'],
            ['code' => 'MOGRA', 'name' => 'Motion Grafis'],
            ['code' => 'PELPRIMA', 'name' => 'Pelayanan Prima'],
            ['code' => 'PGUI', 'name' => 'Pemrograman GUI'],
            ['code' => 'PPB', 'name' => 'Pemrograman Perangkat Bergerak'],
            ['code' => 'PWEB', 'name' => 'Pemrograman Website'],
            ['code' => 'PABP', 'name' => 'Pendidikan Agama dan Budi Pekerti'],
            ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani, Olahraga dan Kesehatan'],
            ['code' => 'PPKN', 'name' => 'Pendidikan Pancasila'],
            ['code' => 'PKAS', 'name' => 'Pengelolaan Kas'],
            ['code' => 'PERGRAF', 'name' => 'Percetakan dan Grafika'],
            ['code' => 'PERNBIS', 'name' => 'Perencanaan Bisnis'],
            ['code' => 'PPJ', 'name' => 'Perencanaan dan Pengalamatan Jaringan'],
            ['code' => 'PERKTEK', 'name' => 'Perkembangan Teknologi'],
            ['code' => 'PAJAK', 'name' => 'Perpajakan'],
            ['code' => 'PERPUS', 'name' => 'Perpustakaan'],
            ['code' => 'PYTHON', 'name' => 'Phyton Programming'],
            ['code' => 'PAK-JDM', 'name' => 'Praktikum Akuntansi Jasa Dagang dan Manufaktur'],
            ['code' => 'PI-ANIMASI', 'name' => 'Produk Iklan Animasi'],
            ['code' => 'IPAS', 'name' => 'Projek IPAS'],
            ['code' => 'PUBSPEAK', 'name' => 'Public Speaking'],
            ['code' => 'SEJ', 'name' => 'Sejarah'],
            ['code' => 'SENBUD', 'name' => 'Seni Budaya'],
            ['code' => 'TAV', 'name' => 'Teknik Audio Video'],
            ['code' => 'TJKN', 'name' => 'Teknologi Jaringan Kabel dan Nirkabel'],
            ['code' => 'TEKPER', 'name' => 'Teknologi Perkantoran'],
            ['code' => 'VIDEO', 'name' => 'Videografi'],
        ];
    }

    public static function getOfficialRooms(): array
    {
        $rooms = [];

        for ($i = 1; $i <= 32; $i++) {
            $rooms[] = [
                'code' => "RUANG-{$i}",
                'name' => "Ruang {$i}",
                'capacity' => 36,
            ];
        }

        for ($i = 1; $i <= 22; $i++) {
            $rooms[] = [
                'code' => "LAB-{$i}",
                'name' => "Lab {$i}",
                'capacity' => 36,
            ];
        }

        $specialRooms = [
            ['code' => 'LAB-IPA', 'name' => 'Lab IPA', 'capacity' => 36],
            ['code' => 'HALL', 'name' => 'Hall', 'capacity' => 100],
            ['code' => 'RUANG-SINIAR', 'name' => 'Ruang Siniar', 'capacity' => 36],
            ['code' => 'OR1', 'name' => 'Lapangan Olahraga 1 (OR1)', 'capacity' => 36],
            ['code' => 'OR2', 'name' => 'Lapangan Olahraga 2 (OR2)', 'capacity' => 36],
            ['code' => 'OR3', 'name' => 'Lapangan Olahraga 3 (OR3)', 'capacity' => 36],
        ];

        return array_merge($rooms, $specialRooms);
    }
}
