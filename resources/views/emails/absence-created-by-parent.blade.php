<x-mail::message>
Szanowni Państwo,

Rodzic {{ $parentFirstName}} {{ $parentLastName }} zgłosił nieobecność dziecka {{ $childFirstName }} {{ $childLastName }} w dniu {{ $absent_at }}.

Informacja została przekazana za pośrednictwem panelu rodzica.

Z wyrazami szacunku,
{{ config('app.name') }}
</x-mail::message>