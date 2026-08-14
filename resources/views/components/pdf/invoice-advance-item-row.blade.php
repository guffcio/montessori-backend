@props([
    'item'
])

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
            border-left: none;
            border-right: none;
            border-bottom: 1px solid #000000;
            border-top: 1px solid #000000;
            width:5%;
        "
    >
        &nbsp;
    </td>
    <td
        style="
            border-left: none;
            border-right: 1px solid #000000;
            border-bottom: 1px solid #000000;
            border-top: 1px solid #000000;
            text-align: left;
            width:56%;
        "
    >
        {{ $item->name }}
    </td>
    <td style="border: 1px solid #000000; text-align: right; width:5%;">{{$item->quantity}}</td>
    <td style="border: 1px solid #000000; text-align: right; width:5%;">szt</td>
    <td style="border: 1px solid #000000; text-align: right; width:13%;">{{ $item->unit_price }} zł</td>
    <td style="border: 1px solid #000000; text-align: right; font-weight: bold; width:13%;">{{ $item->total_price }} zł</td>
</tr>