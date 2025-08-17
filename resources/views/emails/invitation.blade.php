@component('mail::message')
# Undangan Bergabung dengan Proyek

Anda telah diundang untuk bergabung dengan sebuah proyek.
Silakan klik tombol di bawah ini untuk menyelesaikan pendaftaran Anda.

@component('mail::button', ['url' => $url])
Terima Undangan
@endcomponent

Jika Anda mengalami masalah, salin dan tempel URL berikut di browser Anda:<br>
<span class="break-all">{{ $url }}</span>

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent