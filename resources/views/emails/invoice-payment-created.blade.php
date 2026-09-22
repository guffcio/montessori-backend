<x-mail::message>
Szanowni Państwo,

informujemy, że rozpoczęli Państwo proces płatności za fakturę **{{$payment->payable->number}}** z Przedszkola **„Zaczarowany Ogród Montessori”**, jednak nie został on jeszcze zakończony.
        
Jeśli doszło do utraty połączenia, zamknięcia okna lub chcą Państwo po prostu ponowić płatność, mogą Państwo dokończyć ją, korzystając z poniższego przycisku:

<x-mail::button class="button-init" :url="$payment->paymentUrl">
Przejdź do wiadomości
</x-mail::button>

Jeśli przycisk nie działa, skopiuj i otwórz poniższy adres w przeglądarce:

{{ $payment->paymentUrl }}

W przypadku pytań dotyczących płatności lub rozliczeń, prosimy o kontakt z administracją przedszkola.

Z wyrazami szacunku,
{{ config('app.name') }}
</x-mail::message>