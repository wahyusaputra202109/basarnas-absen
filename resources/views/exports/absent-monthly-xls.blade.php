<table>
    <thead>
        <tr>
            <th colspan="8">UNIT KERJA : {{ strtoupper($data['unit']) }}</th>
        </tr>
        <tr>
            <th>NO</th>
            <th>NAMA</th>
            <th>NIP</th>
            <th>HARI</th>
            <th>ABSEN</th>
            <th>TIDAK ABSEN</th>
            <th>WFH</th>
            <th>WFO</th>
            <th>TERLAMBAT</th>
            <th>PULANG SEBELUM WAKTU</th>
        </tr>
    </thead>
    <tbody>
    @foreach($data['data'] as $k => $d)
        <tr>
            <td>{{ ($k+1) }}</td>
            <td>{{ $d['nama'] }}</td>
            <td>`{{ $d['nip'] }}</td>
            <td>{{ $d['hari'] }}</td>
            <td>{{ $d['absen'] }}</td>
            <td>{{ $d['tidakabsen'] }}</td>
            <td>{{ $d['wfh'] }}</td>
            <td>{{ $d['wfo'] }}</td>
            <td>{{ $d['telat'] }}</td>
            <td>{{ $d['pulangcepat'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>