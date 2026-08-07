@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Zaczarowany Ogród Montessori')
<img src="https://przedszkolejedrzejow.pl/img/logo_kolor.png" class="logo" alt="Zaczarowany Ogród Montessori Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
