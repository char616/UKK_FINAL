<x-mail::message>
# Reset Password SCP2

Halo {{ $userName }},

Anda menerima permintaan reset password untuk akun SCP2 Anda.

Gunakan kode OTP berikut untuk melanjutkan proses reset password:

<x-mail::panel>
<div style="text-align: center; font-size: 1.5rem; font-weight: bold; letter-spacing: 0.2em; color: #245A81;">
{{ $otp }}
</div>
</x-mail::panel>

Kode ini berlaku selama 10 menit.

Jika Anda tidak meminta reset password, abaikan email ini.

Terima kasih,
<br>
{{ config('app.name') }}
</x-mail::message>