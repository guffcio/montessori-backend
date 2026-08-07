<x-mail::message>
# 📩 Otrzymałeś nową wiadomość

W Twoim panelu pojawiła się nowa wiadomość od administratora.

<x-mail::panel>
**Temat:** {{ $message->title }}

{{ $preview }}
</x-mail::panel>

<x-mail::button class="button-init" :url="$url">
Przejdź do wiadomości
</x-mail::button>

Jeśli przycisk nie działa, skopiuj i otwórz poniższy adres w przeglądarce:

{{ $url }}

Pozdrawiamy,<br>
{{ config('app.name') }}
</x-mail::message>