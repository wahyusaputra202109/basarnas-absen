<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Employee;
use App\SiagaAssign;
use App\SiagaAbsent;
use App\SiagaShift;
use App\Exports\SiagaAbsentByMonth;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class SiagaAbsentCtrl extends Controller
{

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // find in db
                $emp = Employee::where('nip', $req->nip)
                    ->first();
                if(empty($emp))// or !in_array($emp->position->unit->parent_id, [4]))
                    throw new \Exception("Maaf, NIP / NRP anda tidak terdaftar.");

                // emp TZ
                $tz = $emp->position->unit->tz;

                // set current datetime
                $now = Carbon::now();// $now->subDay();       $now->hour = 6;
                $now = ($tz == 'WITA' ? $now->addHour() : $now);
                $now = ($tz == 'WIT' ? $now->addHours(2) : $now);

                // if($now->hour <5 or $now->hour >18)
                //     throw new \Exception("Absen hanya bisa dilakukan mulai pukul 05.00 s/d 19.00 ". $tz);
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

                // look up employee info to simpeg api
                // $res = Http::timeout(5)->get('http://simpeg.basarnas.go.id/api/pegawai?nip='. $req->nip);
                // if($res->failed())
                //     throw new \Exception("Maaf, NIP / NRP anda tidak terdaftar.");

                // $data = $res->json();

                // look up if employee assigned
                $assign = SiagaAssign::with('shift')
                    ->with('siaga_jabatan')
                    ->where('nip', $req->nip)
                    ->where('mulaimasuk', '<=', $now)
                    ->where('selesaipulang', '>=', $now)
                    ->first();
                if(empty($assign))
                    throw new \Exception("Maaf, NIP / NRP anda tidak di-assign untuk Siaga.");
                $data->shift = $assign->shift->nama;
                $data->siaga_jabatan = $assign->siaga_jabatan->name;

                $minmasuk = $assign->mulaimasuk;
                $maxmasuk = $assign->selesaimasuk;
                $minkeluar = $assign->mulaipulang;
                $maxkeluar = $assign->selesaipulang;

                // inject additional data
                $data->waktu = $now->format('Y,m,d,H,i,s');
                $data->position = $emp->position->name;
                $data->unit = $emp->position->unit->name;
                $data->pesanabsen = ($now->hour <16 ? 'Masuk' : 'Pulang');
                $data->tz = $tz;
                // $data->pesanabsen = ($now->hour <15 ? 'Masuk' : 'Pulang');

                // if($assign->shift =='Shift 1') {
                //     $minmasuk = $assign->tanggal->copy()->setTime(6, 0, 0);
                //     $maxmasuk = $assign->tanggal->copy()->setTime(8, 0, 0);
                //     $minkeluar = $assign->tanggal->copy()->setTime(16, 1, 0);
                //     $maxkeluar = $assign->tanggal->copy()->setTime(20, 0, 0);
                // } else {
                //     $minmasuk = $assign->tanggal->copy()->setTime(19, 1, 0);
                //     $maxmasuk = $assign->tanggal->copy()->setTime(20, 0, 0);
                //     $minkeluar = $assign->tanggal->copy()->setTime(4, 0, 0);
                //     $maxkeluar = $assign->tanggal->copy()->addDay()->setTime(8, 0, 0);
                // }

                $absent = SiagaAbsent::where('nip', $req->nip)
                    // ->whereDate('submitted_at', $maxkeluar->format('Y-m-d'))
                    ->where('submitted_at', '>=', $minmasuk)
                    ->where('submitted_at', '<=', $maxkeluar)
                    ->orderBy('submitted_at', 'ASC')
                    ->first();
                if(!empty($absent) and $absent->submitted_at->hour < $maxkeluar->hour)
                    $data->absenmasukakhir = $absent->submitted_at->format('Y,m,d,H,i,s');
                // if(!empty($absent) and $absent->submitted_at->hour <15)
                //     $data->absenmasukakhir = $absent->submitted_at->format('Y,m,d,H,i,s')`;

                $absent = SiagaAbsent::where('nip', $req->nip)
                    ->where('submitted_at', '>=', $minmasuk)
                    ->where('submitted_at', '<=', $maxkeluar)
                    ->orderBy('submitted_at', 'DESC')
                    ->first();
                if(!empty($absent) and $absent->submitted_at->hour >= $minkeluar->hour)
                    $data->absenkeluarakhir = $absent->submitted_at->format('Y,m,d,H,i,s');
                // if(!empty($absent) and $absent->submitted_at->hour >=15)
                //     $data->absenkeluarakhir = $absent->submitted_at->format('Y,m,d,H,i,s');

                // store absent
                $absent = SiagaAbsent::create([
                    'nip'           => $req->nip,
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

    public function xlsRekap(Request $req)
    {
        $from = Carbon::now()->setMonth($req->bulan)->setYear($req->tahun)->startOfMonth()->startOfDay();
        $to = $from->copy()->endOfMonth()->endOfDay();
        // $from = Carbon::createFromFormat('Y-m-d', $req->from)->startOfDay();
        // $to = Carbon::createFromFormat('Y-m-d', $req->to)->endOfDay();

        $shifts = SiagaShift::all();
        $assigns = DB::table('siaga_assigns AS sa')
            ->selectRaw('sa.id, sa.shift_id, DATE_FORMAT(tanggal, "%e") AS tanggalj, sa.nip, DATE_FORMAT(tanggal, "%Y-%m-%d") AS tanggalymd, DATE_FORMAT(mulaimasuk, "%Y-%m-%d") AS mulaimasukymd, DATE_FORMAT(mulaipulang, "%Y-%m-%d") AS mulaipulangymd, sa.mulaimasuk, sa.selesaimasuk, sa.mulaipulang, sa.selesaipulang, e.name AS empName, l.name AS levelName, sp.name AS siaga_jabatanName')
            ->join('employees AS e', 'e.nip', '=', 'sa.nip')
            ->join('levels AS l', 'l.id', '=', 'e.level_id')
            ->join('positions AS p', 'p.id', '=', 'e.position_id')
            ->join('work_units AS w', 'w.id', '=', 'p.unit_id')
            ->join('siaga_positions AS sp', 'sp.id', '=', 'sa.jabatan_id')
            ->where('sa.tanggal', '>=', $from)
            ->where('sa.tanggal', '<=', $to);
        $assigns = (empty($req->unit_id) ? $assigns : $assigns->where('w.parent_id', $req->unit_id));
        $assigns = (empty($req->level_id) ? $assigns : $assigns->where('l.id', $req->level_id));
        $assigns = $assigns->get();

        // $assigns = SiagaAssign::with('employee')
        //     ->with('employee.level')
        //     ->with('siaga_jabatan')
        //     ->where('tanggal', '>=', $from)
        //     ->where('tanggal', '<=', $to)
        //     ->get();
        $absents = DB::table('siaga_absents')
            ->selectRaw('nip, DATE_FORMAT(submitted_at, "%Y-%m-%d") AS tanggal, DATE_FORMAT(submitted_at, "%H:%i:%s") AS jam, submitted_at')
            ->where('submitted_at', '>=', $from)
            ->where('submitted_at', '<=', $to)
            ->get();

        $dLoop = $from->copy();
        $dates = [];
        $weekends = [];
        while ($dLoop->lte($to)) {
            if($dLoop->isWeekend()) {
                $weekends[] = $dLoop->format('j');
            }
            $dates[] = $dLoop->format('j');
            $dLoop->addDay();
        }

        $holidays = DB::table('holidays')
            ->selectRaw('DATE_FORMAT(holiday_date, "%e") as tanggal')
            ->where('holiday_date', '>=', $from)
            ->where('holiday_date', '<=', $to)
            ->get()
            ->pluck('tanggal')
            ->all();

        $data = [];
        foreach ($assigns as $assign) {
            $tempShift = [];
            foreach ($shifts as $shift) {
                $tempDay = [];
                $count = 0;
                foreach($dates as $date) {
                    $assigned = ($assign->shift_id == $shift->id && $assign->tanggalj == $date);
                    $present = $masuk = $pulang = null;
                    if($assigned) {
                        $present = $absents->filter(function($val, $key) use($assign) {
                                return $val->nip == $assign->nip && $val->tanggal == $assign->tanggalymd;
                            })
                            ->all();

                        $masuk = $absents->filter(function($val, $key) use($assign) {
                                return $val->nip == $assign->nip && $val->tanggal == $assign->mulaimasukymd;
                            })
                            ->first();
                        if(!empty($masuk)) {
                            $mulaimasuk = Carbon::createFromFormat('Y-m-d H:i:s', $assign->mulaimasuk);
                            $selesaimasuk = Carbon::createFromFormat('Y-m-d H:i:s', $assign->selesaimasuk);
                            $dtMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $masuk->submitted_at);
                            $masuk = ($dtMasuk->gte($mulaimasuk) && $dtMasuk->lte($selesaimasuk) ? $masuk : null);
                        }

                        $pulang = $absents->filter(function($val, $key) use($assign) {
                                return $val->nip == $assign->nip && $val->tanggal == $assign->mulaipulangymd;
                            })
                            ->last();
                        if(!empty($pulang)) {
                            $mulaipulang = Carbon::createFromFormat('Y-m-d H:i:s', $assign->mulaipulang);
                            $selesaipulang = Carbon::createFromFormat('Y-m-d H:i:s', $assign->selesaipulang);
                            $dtPulang = Carbon::createFromFormat('Y-m-d H:i:s', $pulang->submitted_at);
                            $pulang = ($dtPulang->gte($mulaipulang) && $dtPulang->lte($selesaipulang) ? $pulang : null);
                        }
                    }
                    $tempDay[$date] = [
                        'masuk'     => empty($masuk) ? '' : $masuk->jam,
                        'pulang'    => empty($pulang) ? '' : $pulang->jam,
                    ];
                    // $tempDay[$date] = ($assigned && !empty($present) ? $date : '');
                    $count += ($assigned && !empty($present) ? 1 : 0);
                }
                // $tempDay[999] = $count;
                // $tempShift[$shift->id] = [
                //     'nama'      => $shift->nama,
                //     'day'       => $tempDay,
                //     'count'     => $count
                // ];
                $data[$assign->nip]['data'][$shift->id]['nama'] = $shift->nama;
                $data[$assign->nip]['data'][$shift->id]['day'][$assign->shift_id] = $tempDay;
                $data[$assign->nip]['data'][$shift->id]['count'][$assign->shift_id] = $count;
            }
            // $data[] = [
            //     'nip'       => $assign->nip,
            //     'nama'      => $assign->empName,
            //     'pangkat'   => $assign->levelName,
            //     'jabatan'   => $assign->siaga_jabatanName,
            //     'data'      => $tempShift,
            // ];

            $data[$assign->nip]['nip'] = $assign->nip;
            $data[$assign->nip]['nama'] = $assign->empName;
            $data[$assign->nip]['pangkat'] = $assign->levelName;
            $data[$assign->nip]['jabatan'] = $assign->siaga_jabatanName;
            // $data[$assign->nip]['data'][$assign->shift_id] = $tempShift;
        }
        $header = [
            'bulan'     => $from->format('m/Y'),
            'days'      => $dates,
            'weekends'  => $weekends,
            'holidays'  => $holidays
        ];

        // return response()->json($data, Response::HTTP_OK);
        return Excel::download(new SiagaAbsentByMonth($header, $data), 'file.xls');
    }

}
