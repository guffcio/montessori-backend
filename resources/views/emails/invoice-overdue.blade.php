<x-mail::message>
Szanowni Państwo,

informujemy o zaległej płatności za fakturę **{{$invoice->number}}** z Przedszkola **„Zaczarowany Ogród Montessori”**.

Termin płatności minął w dniu **{{ $dueDate }}**.

Prosimy o terminowe wpłaty.

W razie pytań dotyczących rozliczeń lub kolejnych faktur pozostajemy do dyspozycji.

Z wyrazami szacunku,
{{ config('app.name') }}
</x-mail::message>