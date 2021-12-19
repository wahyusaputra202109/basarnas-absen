<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Absent;
use App\Employee;
use App\Holiday;
use App\WorkUnit;
use App\Exports\AbsentMonthly;
use App\Exports\AbsentPerUnit;
use App\Exports\AbsentAllUnits;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class AbsenCtrl extends Controller
{
    
    public function time()
    {
        $msgOpen = 'officehour';

        $now = Carbon::now();//       $now->addDay(); $now->hour = 15;
        $msgOpen = ($now->isWeekend() ? 'weekend' : $msgOpen);
        $msgOpen = ($now->hour <5 ? 'before5' : $msgOpen);
        $msgOpen = ($now->hour >18 ? 'after22' : $msgOpen);
        // $msgOpen = ($now->hour <6 ? 'before5' : $msgOpen);
        // $msgOpen = ($now->hour >17 ? 'after22' : $msgOpen);

        // add holiday filter
        $holiday = Holiday::where('holiday_date', $now->format('Y-m-d'))
            ->first();
        $msgOpen = (empty($holiday) ? $msgOpen : 'holiday');

        return response()->json([ 'datetime' => $now->format('Y,m,d,H,i,s'), 'msgOpen' => $msgOpen ], Response::HTTP_OK);
    }

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // set current datetime
                $now = Carbon::now();//       $now->hour = 15;
                if($now->hour <5 or $now->hour >18)
                    throw new \Exception("Absen hanya bisa dilakukan mulai pukul 05.00 s/d 19.00");
                // if($now->hour <6 or $now->hour >17)
                //     throw new \Exception("Absen hanya bisa dilakukan mulai pukul 06.00 s/d 18.00");

                // find in db
                $emp = Employee::where('nip', $req->nip)
                    ->first();
                if(empty($emp) or !in_array($emp->position->unit->parent_id, [1,2,3,4,5,6,7,8,9,10,11,12,16,19,36,61,15,27,34,33,18,22,35,14,30,25,21,24,13,45,32,20,39,37,23,42]))
                    throw new \Exception("Maaf, NIP / NRP anda tidak terdaftar.");

                // look up in simpeg api
                $client = new \GuzzleHttp\Client();
                $res = $client->request(
                    'GET',
                    'http://simpeg.basarnas.go.id/api/pegawai?nip='. $req->nip,
                    [
                        'headers' => [
                            'Content-type' => 'application/json; charset=utf-8',
                            'Accept' => 'application/json',
                        ],
                        'timeout' => 5,
                        'connect_timeout' => 5,
                        'synchronous' => true,
                    ]
                );
                $code = $res->getStatusCode();
                $contents = $res->getBody()->getContents();
                $data = json_decode($contents);
                if($code != '200' or empty($contents))
                    throw new \Exception("Maaf, NIP / NRP anda tidak terdaftar.");

                // inject additional data
                $data->waktu = $now->format('Y,m,d,H,i,s');
                $data->position = $emp->position->name;
                $data->unit = $emp->position->unit->name;
                $data->pesanabsen = ($now->hour <16 ? 'Masuk' : 'Pulang');
                // $data->pesanabsen = ($now->hour <15 ? 'Masuk' : 'Pulang');

                $absenlimit = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d') .' 16:00:00');
                $absent = Absent::where('nip', $req->nip)
                    ->whereDate('submitted_at', $absenlimit->format('Y-m-d'))
                    ->where('submitted_at', '<', $absenlimit)
                    ->orderBy('submitted_at', 'ASC')
                    ->first();
                if(!empty($absent) and $absent->submitted_at->hour <16)
                    $data->absenmasukakhir = $absent->submitted_at->format('Y,m,d,H,i,s');
                // if(!empty($absent) and $absent->submitted_at->hour <15)
                //     $data->absenmasukakhir = $absent->submitted_at->format('Y,m,d,H,i,s');

                    $absent = Absent::where('nip', $req->nip)
                    ->whereDate('submitted_at', $absenlimit->format('Y-m-d'))
                    ->where('submitted_at', '>=', $absenlimit)
                    ->orderBy('submitted_at', 'DESC')
                    ->first();
                if(!empty($absent) and $absent->submitted_at->hour >=16)
                    $data->absenkeluarakhir = $absent->submitted_at->format('Y,m,d,H,i,s');
                // if(!empty($absent) and $absent->submitted_at->hour >=15)
                //     $data->absenkeluarakhir = $absent->submitted_at->format('Y,m,d,H,i,s');
                    
                // store absent
                $absent = Absent::create([
                    'nip' => $req->nip,
                    'workfrom' => $req->wfw,
                    'submitted_at' => $now
                ]);

                return response()->json($data, Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function xls_per_unit(Request $req)
    {
        $from = Carbon::createFromFormat('Y-n-j H:i:s', $req->from);
        $to = Carbon::createFromFormat('Y-n-j H:i:s', $req->to);

        $dLoop = $from->copy();
        $dates = [];
        while ($dLoop->lte($to)) {
            if(!$dLoop->isWeekend()) {
                $dates[] = $dLoop->format('Y-m-d');
            }
            $dLoop->addDay();
        }

        // set ramadhan 2020
        $from = Carbon::createFromFormat('Y-n-j H:i:s', '2020-04-24 00:00:00');
        $to = Carbon::createFromFormat('Y-n-j H:i:s', '2020-05-24 00:00:00');
        $ramadhan20 = [];
        $dLoop = $from->copy();
        while ($dLoop->lte($to)) {
            if(!$dLoop->isWeekend()) {
                $ramadhan20[] = $dLoop->format('Y-m-d');
            }
            $dLoop->addDay();
        }

        $unit_id = $req->unit_id;

        if($unit_id >0) {
            $unit = WorkUnit::find($req->unit_id);

            $employees = $unit->employeesByParent()->get();

            $data = [];
            $data['unit'] = $unit->name;
            foreach ($employees as $kEmp => $vEmp) {
                $data['data'][$kEmp]['nip'] = $vEmp->nip;
                $data['data'][$kEmp]['nama'] = $vEmp->name;

                foreach ($dates as $dateKey => $dateVal) {
                    $maxmasuk = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 08:00:00');
                    $minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:00:00');
                    if(in_array($dateVal, $ramadhan20))
                        $minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 15:00:00');

                    $masuk = Absent::where('nip', $vEmp->nip)
                        ->whereDate('submitted_at', $minkeluar->format('Y-m-d'))
                        ->where('submitted_at', '<', $minkeluar)
                        ->orderBy('submitted_at', 'ASC')
                        ->first();

                    $keluar = Absent::where('nip', $vEmp->nip)
                        ->whereDate('submitted_at', $minkeluar->format('Y-m-d'))
                        ->where('submitted_at', '>=', $minkeluar)
                        ->orderBy('submitted_at', 'DESC')
                        ->first();

                    $vTelat = (empty($masuk) ? 0 : $maxmasuk->diffInMinutes($masuk->submitted_at, false));
                    $vPulangCepat = (empty($keluar) ? 0 : $keluar->submitted_at->diffInMinutes($minkeluar, false));
                    $data['data'][$kEmp]['data'][$dateKey]['tanggal'] = $maxmasuk->format('d/m/Y');
                    $data['data'][$kEmp]['data'][$dateKey]['masuk'] = (empty($masuk) ? '' : $masuk->submitted_at->format('H:i:s'));
                    $data['data'][$kEmp]['data'][$dateKey]['keluar'] = (empty($keluar) ? '' : $keluar->submitted_at->format('H:i:s'));
                    $data['data'][$kEmp]['data'][$dateKey]['wfh'] = (empty($masuk) ? '' : ($masuk->workfrom == 'WFH' ? 1 : ''));
                    $data['data'][$kEmp]['data'][$dateKey]['wfo'] = (empty($masuk) ? '' : ($masuk->workfrom == 'WFO' ? 1 : ''));
                    $data['data'][$kEmp]['data'][$dateKey]['telat'] = ($vTelat >0 ? $vTelat : '');
                    $data['data'][$kEmp]['data'][$dateKey]['pulangcepat'] = ($vPulangCepat >0 ? $vPulangCepat : '');
                    $data['data'][$kEmp]['data'][$dateKey]['tidakabsen'] = ((empty($masuk) and empty($keluar)) ? 'TRUE' : '');
                }
            }

	        // return response()->json($data, Response::HTTP_OK);
            return Excel::download(new AbsentPerUnit($data), 'file.xls');
        } else {
            $user = auth()->user();
            $units = $user->work_units;

            $data = [];
            foreach ($units as $unit) {
                $employees = $unit->employeesByParent()->get();

                $data[$unit->id]['unit'] = $unit->name;
                foreach ($employees as $kEmp => $vEmp) {
                    $data[$unit->id]['data'][$kEmp]['nip'] = $vEmp->nip;
                    $data[$unit->id]['data'][$kEmp]['nama'] = $vEmp->name;

                    foreach ($dates as $dateKey => $dateVal) {
                        $maxmasuk = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 08:00:00');
                        $minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:00:00');
                        if(in_array($dateVal, $ramadhan20))
                            $minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 15:00:00');

                        $masuk = Absent::where('nip', $vEmp->nip)
                            ->whereDate('submitted_at', $minkeluar->format('Y-m-d'))
                            ->where('submitted_at', '<', $minkeluar)
                            ->orderBy('submitted_at', 'ASC')
                            ->first();

                        $keluar = Absent::where('nip', $vEmp->nip)
                            ->whereDate('submitted_at', $minkeluar->format('Y-m-d'))
                            ->where('submitted_at', '>=', $minkeluar)
                            ->orderBy('submitted_at', 'DESC')
                            ->first();

                        $vTelat = (empty($masuk) ? 0 : $maxmasuk->diffInMinutes($masuk->submitted_at, false));
                        $vPulangCepat = (empty($keluar) ? 0 : $keluar->submitted_at->diffInMinutes($minkeluar, false));
                        $data[$unit->id]['data'][$kEmp]['data'][$dateKey]['tanggal'] = $maxmasuk->format('d/m/Y');
                        $data[$unit->id]['data'][$kEmp]['data'][$dateKey]['masuk'] = (empty($masuk) ? '' : $masuk->submitted_at->format('H:i:s'));
                        $data[$unit->id]['data'][$kEmp]['data'][$dateKey]['keluar'] = (empty($keluar) ? '' : $keluar->submitted_at->format('H:i:s'));
                        $data[$unit->id]['data'][$kEmp]['data'][$dateKey]['wfh'] = (empty($masuk) ? '' : ($masuk->workfrom == 'WFH' ? 1 : ''));
                        $data[$unit->id]['data'][$kEmp]['data'][$dateKey]['wfo'] = (empty($masuk) ? '' : ($masuk->workfrom == 'WFO' ? 1 : ''));
                        $data[$unit->id]['data'][$kEmp]['data'][$dateKey]['telat'] = ($vTelat >0 ? $vTelat : '');
                        $data[$unit->id]['data'][$kEmp]['data'][$dateKey]['pulangcepat'] = ($vPulangCepat >0 ? $vPulangCepat : '');
                        $data[$unit->id]['data'][$kEmp]['data'][$dateKey]['tidakabsen'] = ((empty($masuk) and empty($keluar)) ? 'TRUE' : '');
                    }
                }
            }

            return Excel::download(new AbsentAllUnits($data), 'file.xls');
        }
    }

    public function xls(Request $req)
    {
        $from = Carbon::createFromFormat('Y-n-j H:i:s', $req->from);
        $to = Carbon::createFromFormat('Y-n-j H:i:s', $req->to);
        $unit = WorkUnit::find($req->unit_id);
        // $employees = $unit->employees()->get();
        $employees = $unit->employeesByParent()->get();

        $dLoop = $from->copy();
        $dates = [];
        while ($dLoop->lte($to)) {
            if(!$dLoop->isWeekend()) {
                $dates[] = $dLoop->format('Y-m-d');
            }
            $dLoop->addDay();
        }

        $data = [];
        $data['unit'] = $unit->name;
        foreach ($employees as $emp) {
            $telat = 0;
            $pulangcepat = 0;
            $tidakabsen = 0;
            $absen = 0;
            $wfh = 0;
            $wfo = 0;
            foreach ($dates as $dateKey => $dateVal) {
                $maxmasuk = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 08:00:00');
                $minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:00:00');
                $minkeluarjumat = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:30:00');
                $masuk = Absent::where('nip', $emp->nip)
                    ->whereDate('submitted_at', $dateVal)
                    ->orderBy('submitted_at', 'ASC')
                    ->first();
                $keluar = Absent::where('nip', $emp->nip)
                    ->whereDate('submitted_at', $dateVal)
                    ->orderBy('submitted_at', 'DESC')
                    ->first();

                if(empty($masuk)) {
                    $tidakabsen++;
                } else {
                    $absen++;
                    $wfh += ($masuk->workfrom == 'WFH' ? 1 : 0);
                    $wfo += ($masuk->workfrom == 'WFO' ? 1 : 0);
                    if($masuk->submitted_at->gt($maxmasuk))
                        $telat++;
                    if($masuk->submitted_at->isFriday()) {
                        if($keluar->submitted_at->lt($minkeluarjumat)/* or $masuk->submitted_at->diffInHours($keluar) <8*/)
                            $pulangcepat++;
                    } else {
                        if($keluar->submitted_at->lt($minkeluar)/* or $masuk->submitted_at->diffInHours($keluar) <8*/)
                            $pulangcepat++;
                    }
                }
            }

            $data['data'][] = [
                'nama'          => $emp->name,
                'nip'           => $emp->nip,
                'hari'          => count($dates),
                'absen'         => $absen,
                'tidakabsen'    => $tidakabsen,
                'wfh'           => $wfh,
                'wfo'           => $wfo,
                'telat'         => $telat,
                'pulangcepat'   => $pulangcepat,
            ];
        }

        return Excel::download(new AbsentMonthly($data), 'file.xls');
    }

}
