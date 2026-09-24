<x-mail::message>
Szanowni Państwo,

zbliża się termin płatności za fakturę **{{$invoice->number}}** z Przedszkola **„Zaczarowany Ogród Montessori”**.

Termin płatności ubiega w dniu **{{ $dueDate }}**.

Prosimy o terminowe uregulowanie należności.

W razie pytań dotyczących rozliczeń lub kolejnych faktur pozostajemy do dyspozycji.

Z wyrazami szacunku,
{{ config('app.name') }}
</x-mail::message>