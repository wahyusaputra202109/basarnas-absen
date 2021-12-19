<table>
    @foreach($data as $d)
    <tr>
        <th colspan="11">UNIT KERJA : {{ strtoupper($d['unit']) }}</th>
    </tr>
    <tr>
        <th>NO</th>
        <th>NAMA</th>
        <th>NIP</th>
        <th>HARI</th>
        <th>TANGGAL</th>
        <th>ABSEN MASUK</th>
        <th>ABSEN PULANG</th>
        <th>WFH</th>
        <th>WFO</th>
        <th>TERLAMBAT</th>
        <th>PULANG SEBELUM WAKTU</th>
        <th>KETERANGAN</th>
    </tr>
        @php
            $tmpNip = '';
        @endphp
        @foreach($d['data'] as $kData => $vData)
            @foreach($vData['data'] as $kItem => $vItem)
    <tr>
        <td>{{ ($tmpNip != $vData['nip'] ? ($kData+1) : '') }}</td>
        <td>{{ ($tmpNip != $vData['nip'] ? $vData['nama'] : '') }}</td>
        <td>{{ ($tmpNip != $vData['nip'] ? '`'. $vData['nip'] : '') }}</td>
        <td>{{ ($kItem+1) }}</td>
        <td>{{ $vItem['tanggal'] }}</td>
        <td>{{ $vItem['masuk'] }}</td>
        <td>{{ $vItem['keluar'] }}</td>
        <td>{{ $vItem['wfh'] }}</td>
        <td>{{ $vItem['wfo'] }}</td>
        <td>{{ $vItem['telat'] }}</td>
        <td>{{ $vItem['pulangcepat'] }}</td>
        <td>{{ $vItem['tidakabsen'] }}</td>
    </tr>
            @php
                $tmpNip = $vData['nip'];
            @endphp
            @endforeach
        @endforeach

    @endforeach
</table>
