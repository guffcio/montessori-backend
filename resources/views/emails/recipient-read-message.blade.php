<x-mail::message>
# 👁️ Wiadomość została odczytana

Informujemy, że rodzic {{ $firstName }} {{ $lastName }} odczytał wiadomość wysłaną przez administratora.

<x-mail::panel>
**Temat:** {{ $message->title }}

**Rodzic:** {{ $firstName }} {{ $lastName }}

**Data odczytu:** {{ now()->format('d.m.Y H:i') }}
</x-mail::panel>

<x-mail::button :url="$url">
Przejdź do wiadomości
</x-mail::button>

Jeśli przycisk nie działa, skopiuj i otwórz poniższy adres w przeglądarce:

{{ $url }}

Z wyrazami szacunku,<br>
{{ config('app.name') }}
</x-mail::message>