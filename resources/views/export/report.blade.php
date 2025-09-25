<table>
    {{-- Header --}}
    <tr>
        <td colspan="4" style="font-weight: bold; font-size: 14px; background: #d9ead3;">
            Rilis {{ $state['year'] % 100 }}{{ str_pad($state['month'], 2, '0', STR_PAD_LEFT) }}
            ({{ \Carbon\Carbon::createFromDate($state['year'], $state['month'])->translatedFormat('F Y') }})
        </td>
    </tr>

    {{-- Summary Tiket --}}
    <tr>
        <th>Jumlah Tiket Dibuat</th>
        <th>Jumlah Tiket Terselesaikan</th>
        <th>Aplikasi Beban Utama</th>
        <th>Server Beban Utama</th>
        <th>Masalah berulang / menyita waktu</th>
    </tr>
    <tr>
        <td>{{ $ticketTotal->count() }}</td>
        <td>{{ $ticketTotal->count() }}</td>

        {{-- aplikasi --}}
        <td>
            @foreach ($ticketByApplication as $app)
                {{ $app->name }} ({{ $app->total }})<br><br>
            @endforeach
        </td>

        {{-- server --}}
        <td>
            @foreach ($ticketByService as $srv)
                {{ $srv->name }} ({{ $srv->total }})<br><br>
            @endforeach
        </td>

        {{-- masalah berulang --}}
        <td>
            @foreach ($ticketDuplicate as $dup)
                {{ $dup->clean_name }} ({{ $dup->total }})<br><br>
            @endforeach
        </td>
    </tr>

    {{-- Aksi untuk mengurangi tiket --}}
    <tr><td colspan="5" style="background: #d9ead3;">Aksi untuk mengurangi tiket</td></tr>
    <tr>
        <td colspan="5">
            {!! $completionReport !!}
        </td>
    </tr>
</table>

