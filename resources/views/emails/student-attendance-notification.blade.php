<x-mail::message>
# Informasi Kehadiran Siswa - SCP2

Halo **{{ $recipientName }}**,

Berikut adalah pembaruan catatan kehadiran KBM pada sistem **School Class Progress (SCP2)**:

<x-mail::panel>
- **Nama Siswa:** {{ $studentName }}
- **Kelas:** {{ $className }}
- **Mata Pelajaran:** {{ $subjectName }}
- **Tanggal:** {{ $date }}
- **Status Kehadiran:** **{{ $status }}**
</x-mail::panel>

Apabila terdapat ketidaksesuaian pencatatan atau memerlukan konfirmasi lebih lanjut, silakan hubungi Guru Pengampu atau Wali Kelas melalui portal SCP2.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>

