<table>
    <tr>
        <th colspan="3">Rekapitulasi Absensi Siaga bulan {{ $header['bulan'] }}</th>
    </tr>
    <tr>
        <th rowspan="3">No.</th>
        <th rowspan="3">Nama</th>
        <th rowspan="3">Golongan</th>
        <th rowspan="3">Jabatan</th>
        <th rowspan="3">Shift</th>
        <th colspan="{{ count($header['days']) *2 }}">Tanggal</th>
        <th rowspan="2">Total</th>
    </tr>
    <tr>
    @foreach ($header['days'] as $day)
        <th colspan="2" style="{{ (in_array($day, $header['weekends']) || in_array($day, $header['holidays']) ? 'color:#f56c6c' : '') }}">{{ $day }}</th>
    @endforeach
    </tr>
    <tr>
        @foreach ($header['days'] as $day)
            <th style="{{ (in_array($day, $header['weekends']) || in_array($day, $header['holidays']) ? 'color:#f56c6c' : '') }}">Masuk</th>
            <th style="{{ (in_array($day, $header['weekends']) || in_array($day, $header['holidays']) ? 'color:#f56c6c' : '') }}">Pulang</th>
        @endforeach
    </tr>
    @php
        $iLoop = 1;
        $tempLoop = 0;
    @endphp
    @foreach ($data as $emp)
        @foreach ($emp['data'] as $shift_id => $shift)
    <tr>
        <td>{{ $iLoop == $tempLoop ? '' : $iLoop }}</td>
        <td>{{ $iLoop == $tempLoop ? '' : $emp['nama'] }}</td>
        <td>{{ $iLoop == $tempLoop ? '' : $emp['pangkat'] }}</td>
        <td>{{ $iLoop == $tempLoop ? '' : $emp['jabatan'] }}</td>
        <td>{{ $shift['nama'] }}</td>
            @if(isset($shift['day'][$shift_id]))
                @foreach ($shift['day'][$shift_id] as $day)
        <td>{{ $day['masuk'] }}</td>
        <td>{{ $day['pulang'] }}</td>
                @endforeach
            @else
                @foreach ($header['days'] as $day)
        <td></td>
        <td></td>
                @endforeach
            @endif
        <td>{{ isset($shift['count'][$shift_id]) ? $shift['count'][$shift_id] : 0 }}</td>
            @php
                $tempLoop = $iLoop;
            @endphp
        @endforeach
    </tr>
    @php
        $iLoop++;
    @endphp
    @endforeach
</table>
