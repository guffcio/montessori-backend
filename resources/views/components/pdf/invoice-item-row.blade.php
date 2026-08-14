@props([
    'item',
    'index',
])

<tr>
    <td style="border: 1px solid #000000;width:3%;">{{ $index }}</td>

    <td style="border: 1px solid #000000;width:5%;">&nbsp;</td>

    <td style="text-align: left; border: 1px solid #000000; width:56%;">{{$item->name}}</td>

    <td style="text-align: right; border: 1px solid #000000; width:5%;">{{ $item->quantity }}</td>

    <td style="text-align: center; border: 1px solid #000000; width:5%;">szt</td>

    <td style="text-align: right; border: 1px solid #000000; width:13%;">{{ $item->unit_price }} zł</td>

    <td style="text-align: right; border: 1px solid #000000; width:13%;">{{ $item->total_price }} zł</td>
</tr>