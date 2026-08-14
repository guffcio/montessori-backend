<!doctype html>
<html lang="pl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Faktura VAT nr {{$invoice->invoice_sequence}}/{{$invoice->invoice_month}}/{{ $invoice->invoice_year }}</title>

        <style>
            @page {
                margin-bottom: 25mm;
            }

            body {
                margin: 0;
            }
        </style>
    </head>

    <body>
        <div style="font-family: DejaVu Sans, sans-serif; padding-bottom: 5px; border-bottom: 1px solid #000000">
            <div style="width: 50%; float: left">
                <img src="https://przedszkolejedrzejow.pl/panel/img/faktura_logo.png" style="width: 50%" />
            </div>
            <div style="width: 50%; float: left">
                <div style="text-align: center; font-weight: bold; font-size: 11px"></div>

                <div style="font-style: italic; font-size: 11px">Sprzedawca/Podatnik:</div>
                <div style="font-size: 11px; font-weight: bold">"ZACZAROWANY OGRÓD MONTESSORI"</div>
                <div style="font-size: 11px">
                    Niepubliczne przedszkole i klub dziecięcy<br />Zdzisława Nyk<br />ul.Dygasińskiego 126 28-300
                    Jędrzejów<br />NIP: 656-104-72-61
                </div>

                <div style="font-size: 11px">Bank: PKO BP Oddział w Jędrzejowie</div>
                <div style="font-size: 11px">Konto: 45 1020 2733 0000 2502 0096 1425</div>
            </div>
            <div style="clear: both"></div>
        </div>
        <div style="clear: both"></div>

        <div style="font-family: DejaVu Sans, sans-serif; padding-top: 10px">
            <div style="font-size: 18px; font-weight: bold; text-align: center">FAKTURA VAT {{$invoice->invoice_sequence}}/{{$invoice->invoice_month}}/{{ $invoice->invoice_year }}</div>
            <div style="font-size: 11px; text-align: center">kopia / oryginał *)</div>

            <div style="margin-top: 10px; font-family: DejaVu Sans">
                <div style="width: 50%; float: left">
                    <div style="font-size: 11px">
                        <span style="font-size: 11px; font-style: italic; line-height: 20px">Nabywca:<br /></span>
                        @foreach ($invoice->parentSnapshots as $parentSnapshot)
                            {{ $parentSnapshot->first_name }} {{ $parentSnapshot->last_name }}
                            <br />
                            Adres:
                            {{ $parentSnapshot->street }}
                            {{ $parentSnapshot->house_number }}{{ $parentSnapshot->apartment_number ? "/{$parentSnapshot->apartment_number}" : "" }},
                            {{ $parentSnapshot->postal_code }}
                            {{ $parentSnapshot->city }}
                            <br /><br />
                        @endforeach
                        
                    </div>
                </div>
                <div style="width: 50%; float: left">
                    <div style="font-size: 11px; text-align: right">
                        <span style="font-size: 11px; font-style: italic">Data wystawienia:</span> {{$invoice->issue_date->format('Y-m-d')}}<br />
                        <span style="font-size: 11px; font-style: italic">Za okres:</span> {{ $billingMonthName }} {{ $invoice->billing_date->year }}

                        <div style="text-align: right; margin-top: 10px">
                            <span style="font-size: 11px; font-style: italic;">Sposób zapłaty:</span>
                            przelew / karta *)<br />
                            <span style="font-size: 11px; font-style: italic;">Termin zapłaty:</span>
                            {{ $invoice->due_date->format('Y-m-d') }}
                        </div>
                    </div>
                </div>
                <div style="clear: both"></div>
            </div>

            <div style="margin-top: 20px">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px" border="0" cellpadding="3">
                    <tbody>
                        <tr>
                            <td style="text-align: center; border: 1px solid #000000; width:3%;">Lp.</td>
                            <td style="text-align: center; border: 1px solid #000000; width:5%;">PKWiU</td>
                            <td style="text-align: center; border: 1px solid #000000; width:56%">Nazwa</td>
                            <td style="text-align: center; border: 1px solid #000000; width:5%;">Ilość</td>
                            <td style="text-align: center; border: 1px solid #000000; width:5%;">Jm</td>
                            <td style="text-align: center; border: 1px solid #000000; width:13%;">Cena<br />jednostkowa</td>
                            <td style="text-align: center; border: 1px solid #000000; width:13%;">Wartość</td>
                        </tr>

                    @php
                 
                    @endphp

                    @foreach ($chargeAndDiscountItems as $item)
                        <x-pdf.invoice-item-row
                            :item="$item"
                            :index="$loop->iteration"
                        />
                    @endforeach

                        <tr>
                            <td
                                style="
                                    border-right: none;
                                    border-left: 1px solid #000000;
                                    border-bottom: 1px solid #000000;
                                    border-top: 1px solid #000000;
                                    width:3%;
                                "
                            >
                                &nbsp;
                            </td>
                            <td
                                style="
                                    border-right: none;
                                    border-left: none;
                                    border-bottom: 1px solid #000000;
                                    border-top: 1px solid #000000;
                                    width:5%;
                                "
                            >
                                &nbsp;
                            </td>
                            <td
                                style="
                                    border-right: none;
                                    border-left: none;
                                    border-bottom: 1px solid #000000;
                                    border-top: 1px solid #000000;
                                    width:56%
                                "
                            >
                                &nbsp;
                            </td>
                            <td
                                style="
                                    border-right: none;
                                    border-left: none;
                                    border-bottom: 1px solid #000000;
                                    border-top: 1px solid #000000;
                                    width:5%;
                                "
                            >
                                &nbsp;
                            </td>
                            <td
                                style="
                                    border-right: none;
                                    border-left: none;
                                    border-bottom: 1px solid #000000;
                                    border-top: 1px solid #000000;
                                    width:5%;
                                "
                            >
                                &nbsp;
                            </td>
                            <td
                                style="
                                    border-right: none;
                                    border-left: none;
                                    border-bottom: 1px solid #000000;
                                    border-top: 1px solid #000000;
                                    text-align: right;
                                    font-weight: bold;
                                    width:13%;
                                "
                            >
                                RAZEM:
                            </td>
                            <td
                                style="
                                    text-align: center;
                                    border: 1px solid #000000;
                                    text-align: right;
                                    font-weight: bold;
                                    width:13%;
                                "
                            >
                                {{ $invoice->subtotalBeforeAdvances()}} zł
                            </td>
                        </tr>

                        <tr>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:3%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:5%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:56%">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:5%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:5%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:13%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:13%;">
                                &nbsp;
                            </td>
                        </tr>

                        <tr>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:3%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:5%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:56%">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:5%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:5%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:13%;">
                                &nbsp;
                            </td>
                            <td style="border-right: none; border-left: none; border-bottom: none; border-top: none; width:13%;">
                                &nbsp;
                            </td>
                        </tr>

                        @foreach ($advanceItems as $item)
                            <x-pdf.invoice-advance-item-row :item="$item" />
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px">
                <div style="font-size: 13px">
                    <span style="font-size: 13px; font-style: italic">Słownie:</span>
                    {{ $amountInWords }}
                </div>
                <div style="text-decoration: underline; font-size: 18px; font-weight: bold; margin-top: 5px">
                    Do zapłaty: {{ $invoice->total_amount }} zł
                </div>
            </div>

            <div style="margin-top: 40px">
                <div style="width: 50%; float: left">
                    ..................................................
                    <div style="font-size: 10px; font-style: italic">
                        Podpis osoby upoważnionej do wystawienia faktury
                    </div>
                </div>

                <div style="width: 50%; float: left; text-align: right">
                    ..................................................
                    <div style="font-size: 10px; font-style: italic">Podpis osoby upoważnionej do odbioru faktury</div>
                </div>
            </div>

              <div style="clear: both"></div>

            <div style="margin-top: 40px; font-size: 11px">*) Niepotrzebne skreślić</div>
        </div>

        <script type="text/php">
            if (isset($pdf)) {
                $pdf->page_text(
                    500,
                    800,
                    "Strona {PAGE_NUM}/{PAGE_COUNT}",
                    null,
                    10
                );
            }
        </script>
    </body>
</html>
