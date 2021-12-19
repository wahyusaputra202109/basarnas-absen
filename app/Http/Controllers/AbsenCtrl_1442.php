<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Absent;
use App\AbsentDup;
use App\Employee;
use App\Holiday;
use App\WorkUnit;
use App\Exports\AbsentMonthly;
use App\Exports\AbsentPerUnit;
use App\Exports\AbsentAllUnits;
use App\Exports\AbsentByUnit;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class AbsenCtrl extends Controller
{

    public function time($tz = false)
    {
        $msgOpen = 'officehour';

        $now = Carbon::now();//       $now->subDay(); $now->hour = 6;
        $now = ($tz == 'WITA' ? $now->addHour() : $now);
        $now = ($tz == 'WIT' ? $now->addHours(2) : $now);

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
                // find in db
                $emp = Employee::where('nip', $req->nip)
                    ->first();
                // if(empty($emp) or !in_array($emp->position->unit->parent_id, [1,2,3,4,5,6,7,8,9,10,11,12,16,19,36,61,15,27,34,33,18,22,35,14,30,25,21,24,13,45,32,20,39,37,23,42]))
                //     throw new \Exception("Maaf, NIP / NRP anda tidak terdaftar.");

                // emp TZ
                $tz = $emp->position->unit->tz;

                // set current datetime
                $now = Carbon::now();// $now->subDay();       $now->hour = 6;
                $now = ($tz == 'WITA' ? $now->addHour() : $now);
                $now = ($tz == 'WIT' ? $now->addHours(2) : $now);

                if($now->hour <5 or $now->hour >18)
                    throw new \Exception("Absen hanya bisa dilakukan mulai pukul 05.00 s/d 19.00 ". $tz);
                // if($now->hour <6 or $now->hour >17)
                //     throw new \Exception("Absen hanya bisa dilakukan mulai pukul 06.00 s/d 18.00");

                // look up in simpeg api
                $client = new \GuzzleHttp\Client();
                $res = $client->request(
                    'GET',
                    'https://simpeg.basarnas.go.id/api/pegawai?nip='. $req->nip,
                    [
                        'auth' => [ 'apisimpeg', 'bismillah' ],
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
                //$data->pesanabsen = ($now->hour <16 ? 'Masuk' : 'Pulang');
				$data->pesanabsen = ($now->hour <15 ? 'Masuk' : 'Pulang');
                $data->tz = $tz;
                // $data->pesanabsen = ($now->hour <15 ? 'Masuk' : 'Pulang');

                //$absenlimit = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d') .' 16:00:00');
				$absenlimit = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d') .' 15:00:00');
                $absent = Absent::where('nip', $req->nip)
                    ->whereDate('submitted_at', $absenlimit->format('Y-m-d'))
                    ->where('submitted_at', '<', $absenlimit)
                    ->orderBy('submitted_at', 'ASC')
                    ->first();
                //if(!empty($absent) and $absent->submitted_at->hour <16)
				if(!empty($absent) and $absent->submitted_at->hour <15)
                    $data->absenmasukakhir = $absent->submitted_at->format('Y,m,d,H,i,s');
                // if(!empty($absent) and $absent->submitted_at->hour <15)
                //     $data->absenmasukakhir = $absent->submitted_at->format('Y,m,d,H,i,s');

                    $absent = Absent::where('nip', $req->nip)
                    ->whereDate('submitted_at', $absenlimit->format('Y-m-d'))
                    ->where('submitted_at', '>=', $absenlimit)
                    ->orderBy('submitted_at', 'DESC')
                    ->first();
                //if(!empty($absent) and $absent->submitted_at->hour >=16)
				if(!empty($absent) and $absent->submitted_at->hour >=15)
                    $data->absenkeluarakhir = $absent->submitted_at->format('Y,m,d,H,i,s');
                // if(!empty($absent) and $absent->submitted_at->hour >=15)
                //     $data->absenkeluarakhir = $absent->submitted_at->format('Y,m,d,H,i,s');

                // store absent
                $absent = Absent::create([
                    'nip'           => $req->nip,
                    'workfrom'      => $req->wfw,
                    'submitted_at'  => $now,
                    'tz'            => $tz
                ]);

                $absent = AbsentDup::create([
                    'nip'           => $req->nip,
                    'workfrom'      => $req->wfw,
                    'submitted_at'  => $now,
                    'tz'            => $tz
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
                    //$minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:00:00');
                    //if(in_array($dateVal, $ramadhan20))
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
                        //$minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:00:00');
                        //if(in_array($dateVal, $ramadhan20))
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

    public function getEmployee($id, $from, $to)
    {
        $emp = Employee::find($id);
        $from = Carbon::createFromFormat('Y-n-j H:i:s', $from .' 00:00:00');
        $to = Carbon::createFromFormat('Y-n-j H:i:s', $to .' 23:59:59');

        $data = AbsentDup::where('nip', $emp->nip)
            ->where('submitted_at', '>=', $from)
            ->where('submitted_at', '<=', $to)
            ->orderBy('submitted_at', 'ASC')
            ->get();

        return response($data->jsonSerialize(), Response::HTTP_OK);
    }

    public function storeEmployee(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                if($req->id != 0) {
                    // set current datetime
                    $now = Carbon::createFromFormat('Y-m-d H:i:s', $req->waktu);

                    if($now->hour <5 or $now->hour >18)
                        throw new \Exception("Absen hanya bisa dilakukan mulai pukul 05.00 s/d 19.00 ". $tz);

                    $absent = AbsentDup::find($req->id);
                    $absent->submitted_at = $now;
                    $absent->save();
                } else {
                    // find in db
                    $emp = Employee::find($req->emp_id);

                    // emp TZ
                    $tz = $emp->position->unit->tz;

                    // set current datetime
                    $now = Carbon::createFromFormat('Y-m-d H:i:s', $req->waktu);

                    if($now->hour <5 or $now->hour >18)
                        throw new \Exception("Absen hanya bisa dilakukan mulai pukul 05.00 s/d 19.00 ". $tz);

                    // store absent
                    $absent = AbsentDup::create([
                        'nip'           => $emp->nip,
                        'workfrom'      => $req->wfw,
                        'submitted_at'  => $now,
                        'tz'            => $tz
                    ]);
                }

                return response()->json($absent, Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function xlsUnit(Request $req)
    {
        $from = Carbon::createFromFormat('Y-m-d H:i:s', $req->from .' 00:00:00');
        $to = Carbon::createFromFormat('Y-m-d H:i:s', $req->to .' 23:59:59');

        $dLoop = $from->copy();
        $dates = [];
        while ($dLoop->lte($to)) {
            if(!$dLoop->isWeekend()) {
                $dates[] = $dLoop->format('Y-m-d');
            }
            $dLoop->addDay();
        }

        // set ramadhan 2020
        $from = Carbon::createFromFormat('Y-m-d H:i:s', '2020-04-24 00:00:00');
        $to = Carbon::createFromFormat('Y-m-d H:i:s', '2020-05-24 00:00:00');
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
                    //$minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:00:00');
                    //if(in_array($dateVal, $ramadhan20))
                        $minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 15:00:00');

                    $masuk = AbsentDup::where('nip', $vEmp->nip)
                        ->whereDate('submitted_at', $minkeluar->format('Y-m-d'))
                        ->where('submitted_at', '<', $minkeluar)
                        ->orderBy('submitted_at', 'ASC')
                        ->first();

                    $keluar = AbsentDup::where('nip', $vEmp->nip)
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
                        //$minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:00:00');
                        //if(in_array($dateVal, $ramadhan20))
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
                //$minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:00:00');
				$minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 15:00:00');
                //$minkeluarjumat = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 16:30:00');
				$minkeluarjumat = Carbon::createFromFormat('Y-m-d H:i:s', $dateVal .' 15:30:00');
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

    public function apiGenReport($unit_id, $from, $to)
    {
        // output
        $data = [
            'result'    => '',
            'error'     => [],
            'success'   => []
        ];

        try {
            $unit_id = $unit_id;
            $from = Carbon::createFromFormat('Y-m-d H:i:s', $from .' 00:00:00');
            $to = Carbon::createFromFormat('Y-m-d H:i:s', $to .' 23:59:59');

            // allow only Denpasar => 18
            $unit_id = 18;

            $unit = WorkUnit::find($unit_id);
            $emps = $unit->employeesByParent()->get();
            $rpts = $unit->genReport($from, $to)->get();

            // generate date-range
            $dLoop = $from->copy();
            $dates = [];
            while ($dLoop->lte($to)) {
                if(!$dLoop->isWeekend()) {
                    $dates[] = $dLoop->format('Y-m-d');
                }
                $dLoop->addDay();
            }

            // set ramadhan 2020
            $from = Carbon::createFromFormat('Y-m-d H:i:s', '2020-04-24 00:00:00');
            $to = Carbon::createFromFormat('Y-m-d H:i:s', '2020-05-24 00:00:00');
            $ramadhan20 = [];
            $dLoop = $from->copy();
            while ($dLoop->lte($to)) {
                if(!$dLoop->isWeekend()) {
                    $ramadhan20[] = $dLoop->format('Y-m-d');
                }
                $dLoop->addDay();
            }

            $dataEmps = [];
            foreach ($emps as $kEmp => $vEmp) {
                foreach ($dates as $kDate => $vDate) {
                    $maxmasuk = Carbon::createFromFormat('Y-m-d H:i:s', $vDate .' 08:00:00');
                    //$minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $vDate .' 16:00:00');
                    //if(in_array($vDate, $ramadhan20))
                        $minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $vDate .' 15:00:00');

                    $masuk = $rpts->filter(function($val, $key) use($vEmp, $maxmasuk) {
                            $submitted_at = Carbon::createFromFormat('Y-m-d H:i:s', $val->submitted_at);
                            return $val->nip == $vEmp->nip and
                                $submitted_at->copy()->startOfDay()->eq($maxmasuk->copy()->startOfDay()) and
                                $submitted_at->lte($maxmasuk);
                        })
                        ->sortBy('submitted_at')
                        ->values()
                        ->first();

                    $keluar = $rpts->filter(function($val, $key) use($vEmp, $minkeluar) {
                            $submitted_at = Carbon::createFromFormat('Y-m-d H:i:s', $val->submitted_at);
                            return $val->nip == $vEmp->nip and
                                $submitted_at->copy()->startOfDay()->eq($minkeluar->copy()->startOfDay()) and
                                $submitted_at->gte($minkeluar);
                        })
                        ->sortBy('submitted_at')
                        ->values()
                        ->first();

                    $dMasuk = (empty($masuk) ? null : Carbon::createFromFormat('Y-m-d H:i:s', $masuk->submitted_at));
                    $dKeluar = (empty($keluar) ? null : Carbon::createFromFormat('Y-m-d H:i:s', $keluar->submitted_at));
                    $vTelat = (empty($masuk) ? 0 : $maxmasuk->diffInMinutes($dMasuk, false));
                    $vPulangCepat = (empty($keluar) ? 0 : $dKeluar->diffInMinutes($minkeluar, false));
                    $outRpt[$kDate] = [
                        'tanggal'       => $maxmasuk->format('d/m/Y'),
                        'masuk'         => (empty($masuk) ? '' : $dMasuk->format('H:i:s')),
                        'keluar'        => (empty($keluar) ? '' : $dKeluar->format('H:i:s')),
                        'wfh'           => (empty($masuk) ? '' : ($masuk->workfrom == 'WFH' ? 1 : '')),
                        'wfo'           => (empty($masuk) ? '' : ($masuk->workfrom == 'WFO' ? 1 : '')),
                        'telat'         => ($vTelat >0 ? $vTelat : ''),
                        'pulangcepat'   => ($vPulangCepat >0 ? $vPulangCepat : ''),
                        'tidakabsen'    => ((empty($masuk) and empty($keluar)) ? 'TRUE' : '')
                    ];
                }
                $dataEmps[$kEmp] = [
                    'nip'       => $vEmp->nip,
                    'nama'      => $vEmp->name,
                    'data'      => $outRpt
                ];
            }
            $data['result'] = 'success';
            $data['success']['values'] = [
                'unit'      => $unit->name,
                'employees' => $dataEmps
            ];

            return response()->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            $data['result'] = 'error';
            $data['success'] = [];
            $data['error']['message'] = $e->getMessage();

            return response()->json($data, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function xlsUnit_v2(Request $req)
    {
        $from = Carbon::createFromFormat('Y-n-j H:i:s', $req->from);
        $to = Carbon::createFromFormat('Y-n-j H:i:s', $req->to);
        // $from = Carbon::createFromFormat('Y-m-d H:i:s', '2020-09-01 00:00:00');
        // $to = Carbon::createFromFormat('Y-m-d H:i:s', '2020-09-30 23:59:59');

        $dLoop = $from->copy();
        $dates = [];
        while ($dLoop->lte($to)) {
            if(!$dLoop->isWeekend()) {
                $dates[] = $dLoop->format('Y-m-d');
            }
            $dLoop->addDay();
        }

        // set ramadhan 2020
        $fromRmd = Carbon::createFromFormat('Y-m-d H:i:s', '2020-04-24 00:00:00');
        $toRmd = Carbon::createFromFormat('Y-m-d H:i:s', '2020-05-24 00:00:00');
        $ramadhan20 = [];
        $dLoop = $fromRmd->copy();
        while ($dLoop->lte($toRmd)) {
            if(!$dLoop->isWeekend()) {
                $ramadhan20[] = $dLoop->format('Y-m-d');
            }
            $dLoop->addDay();
        }

        $unit_id = $req->unit_id;
        // $unit_id = 4;
        $unit = $unit_id >0 ? WorkUnit::find($unit_id) : WorkUnit::where('parent_id', $unit_id)->get();

        $emps = DB::table('employees AS e')
            ->select('e.nip', 'e.name', 'p.name AS jabatan', 'w.id AS unit_id', 'w.name AS unit')
            ->join('positions AS p', 'p.id', '=', 'e.position_id')
            ->join('work_units AS w', 'w.id', '=', 'p.unit_id')
            ->orderBy('w.id', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->orderBy('p.order_id', 'ASC')
            ->orderBy('e.nip', 'ASC');

        $data = DB::table('absents AS a')
            ->selectRaw('e.nip, DATE_FORMAT(a.submitted_at, "%Y-%m-%d") AS tanggal, workfrom, MIN(a.submitted_at) AS masuk, MAX(a.submitted_at) AS pulang')
            ->join('employees AS e', 'e.nip', '=', 'a.nip')
            ->join('positions AS p', 'p.id', '=', 'e.position_id')
            ->join('work_units AS w', 'w.id', '=', 'p.unit_id')
            ->where('a.submitted_at', '>=', $from)
            ->where('a.submitted_at', '<=', $to)
            ->groupBy('e.nip')
            ->groupBy('tanggal');

        if($unit_id >0) {
            $emps = $emps->where('w.parent_id', $unit_id);
            $data = $data->where('w.parent_id', $unit_id);
        } else {
            $ids = $unit->pluck('id')->all();
            $emps = $emps->whereIn('w.parent_id', $ids);
            $data = $data->whereIn('w.parent_id', $ids);
        }

        $emps = $emps->get();
        $data = $data->get();

        $rpt = [];
        foreach ($emps as $emp) {
            $temps = [];
            foreach($dates as $vDate) {
                $maxmasuk = Carbon::createFromFormat('Y-m-d H:i:s', $vDate .' 08:00:00');
                //$minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $vDate .' 16:00:00');
                //if(in_array($vDate, $ramadhan20))
                    $minkeluar = Carbon::createFromFormat('Y-m-d H:i:s', $vDate .' 15:00:00');

                $filtered = $data->filter(function($val, $key) use($emp, $vDate) {
                        return $emp->nip == $val->nip && $val->tanggal == $vDate;
                    })
                    ->first();

                $masuk = '';
                $keluar = '';
                $vTelat = 0;
                $vPulangCepat = 0;
                if(!empty($filtered)) {
                    $masuk = Carbon::createFromFormat('Y-m-d H:i:s', $filtered->masuk);
                    $keluar = Carbon::createFromFormat('Y-m-d H:i:s', $filtered->pulang);

                    if($masuk->gte($minkeluar)) { // jam masuk tidak melebihi jam minimal keluar
                        $masuk = '';
                    } elseif($masuk->gt($maxmasuk)) { // jam masuk melebihi jam maksimal masuk (telat)
                        $vTelat = $maxmasuk->diffInMinutes($masuk, false);
                        $masuk = $masuk->format('H:i:s');
                    } else {
                        $masuk = $masuk->format('H:i:s');
                    }
                    if($keluar->lt($minkeluar)) { // jam keluar belum masuk sesuai jam minimal keluar
                        $keluar = '';
                    } else {
                        $keluar = $keluar->format('H:i:s');
                    }
                    // if($masuk->ne($keluar)) {
                    //     if($masuk->gt($maxmasuk))
                    //         $vTelat = $maxmasuk->diffInMinutes($masuk, false);
                    //     $keluar = $keluar->gte($minkeluar) ? $keluar->format('H:i:s') : '';
                    // } else {
                    //     $keluar = '';
                    // }
                    // $masuk = $masuk->format('H:i:s');
                }
                $temps[] = [
                    'tanggal'       => $maxmasuk->format('d/m/Y'),
                    'masuk'         => $masuk,
                    'keluar'        => $keluar,
                    'wfh'           => (empty($filtered) ? '' : ($filtered->workfrom == 'WFH' ? 1 : '')),
                    'wfo'           => (empty($filtered) ? '' : ($filtered->workfrom == 'WFO' ? 1 : '')),
                    'telat'         => ($vTelat >0 ? $vTelat : ''),
                    'pulangcepat'   => ($vPulangCepat >0 ? $vPulangCepat : ''),
                    'tidakabsen'    => ((empty($filtered)) ? 'TRUE' : '')
                ];
            }
            $rpt[$emp->unit_id]['unit'] = $emp->unit;
            $rpt[$emp->unit_id]['data'][] = [
                'nip'       => $emp->nip,
                'nama'      => $emp->name,
                'data'      => $temps
            ];
        }

        // return response()->json($rpt, Response::HTTP_OK);
        return Excel::download(new AbsentByUnit($rpt), 'file.xls');
    }

}
