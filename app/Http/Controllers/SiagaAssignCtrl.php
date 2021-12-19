<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\SiagaAssign;
use App\SiagaShift;
use App\Holiday;
use Carbon\Carbon;
use DB;

class SiagaAssignCtrl extends Controller
{

    public function dt(Request $req, SiagaAssign $assign)
    {
        // build query
        $data = $assign->newQuery();
        // apply filter
        if(!empty($req->search)) {
            $data->where('nip', 'LIKE', '%'. $req->search .'%');
        }
        // get count before limit
        $count = $data->count();
        // apply order
        if(isset($req->sort)) {
            $sorts = $req->sort;
            foreach ($sorts as $sort) {
                $val = explode('|', $sort);
                $data = $data->orderBy($val[0], $val[1]);
            }
        }
        // apply limit
        $data = $data->offset(($req->page -1) *$req->viewData)->limit($req->viewData);
        // real data
        $data->with('employee');
        $data->with('employee.position');
        $data->with('employee.level');
        $data->with('shift');
        $data->with('siaga_jabatan');
        $data = $data->get();

        // build result
        $out = [
            'data'      => $data->toArray(),
            'from'      => ($req->page -1) *$req->viewData +1,
            'to'        => $data->count(),
            'totalData' => $count,
            'totalPage' => ceil($count /$req->viewData)
        ];
        return response()->json($out, Response::HTTP_OK);
    }

    public function get($id)
    {
        $assign = SiagaAssign::with('employee')
            ->with('employee.position')
            ->with('employee.level')
            ->where('id', $id)
            ->first();

        $out = $assign->toArray();
        $out['tanggal_f'] = $assign->tanggal->format('d/m/Y');

        return response()->json($out, Response::HTTP_OK);
    }

    public function store(Request $req)
    {
        return DB::transaction(function() use($req) {
            try {
                // validations
                $validatorArr = [
                    'nip'           => 'required',
                    'tanggal'       => 'required',
                    'shift_id'      => 'required',
                    'jabatan_id'    => 'required',
                ];
                $validator = Validator::make($req->all(), $validatorArr);
                if($validator->fails()) {
                    $errorMsg = "> ". implode("\n> ", $validator->errors()->all());
                    throw new \Exception($errorMsg);
                }

                // get shift
                $shift = SiagaShift::find($req->shift_id);
                // init date & time
                $mulaimasuk = Carbon::createFromFormat('d/m/Y H:i:s', $req->tanggal .' '. $shift->mulaimasuk);
                $selesaimasuk = Carbon::createFromFormat('d/m/Y H:i:s', $req->tanggal .' '. $shift->selesaimasuk);
                $mulaipulang = Carbon::createFromFormat('d/m/Y H:i:s', $req->tanggal .' '. $shift->mulaipulang);
                $selesaipulang = Carbon::createFromFormat('d/m/Y H:i:s', $req->tanggal .' '. $shift->selesaipulang);
                if($mulaimasuk->gt($mulaipulang)) {
                    $mulaipulang->addDay();
                    $selesaipulang->addDay();
                }
                // store user
                $assign = SiagaAssign::findOrNew($req->id);
                $assign->nip = $req->nip;
                $assign->tanggal = Carbon::createFromFormat('d/m/Y', $req->tanggal);
                $assign->mulaimasuk = $mulaimasuk;
                $assign->selesaimasuk = $selesaimasuk;
                $assign->mulaipulang = $mulaipulang;
                $assign->selesaipulang = $selesaipulang;
                $assign->shift_id = $req->shift_id;
                $assign->jabatan_id = $req->jabatan_id;
                $assign->keterangan = $req->keterangan;
                $assign->save();

                return response($assign->jsonSerialize(), Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollback();
                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    public function destroy($id)
    {
        SiagaAssign::destroy($id);
        return response(null, Response::HTTP_OK);
    }

    public function getByMonth($month)
    {
        $tanggal = Carbon::now()->setMonth($month)->subMonths(2)->startOfMonth();
        $assign = SiagaAssign::with('employee')
            ->with('employee.level')
            ->with('shift')
            ->with('siaga_jabatan')
            ->where('tanggal', '>=', $tanggal)
            ->get();

        // set end of date
        $month6 = $tanggal->copy()->addMonths(6);
        // init weekends
        $weekends = [];
        $dLoop = $tanggal->copy();
        while ($dLoop->lt($month6)) {
            if($dLoop->isWeekend()) {
                $weekends[] = $dLoop->format('Y-m-d');
            }
            $dLoop->addDay();
        }
        // init holidays
        $holidays = DB::table('holidays')
            ->selectRaw('DATE_FORMAT(holiday_date, "%Y-%m-%d") as tanggal')
            ->where('holiday_date', '>=', $tanggal)
            ->where('holiday_date', '<', $month6)
            ->get()
            ->pluck('tanggal')
            ->all();

        $out = [
            'weekends'  => $weekends,
            'holidays'  => $holidays
        ];
        foreach ($assign as $item) {
            $out['data'][$item->tanggal->format('Y-m-d')][] = [
                'nip'       => $item->nip,
                'nama'      => $item->employee->name,
                'namashort' => Str::limit($item->employee->name, 12, '...'),
                'level'     => $item->employee->level->name,
                'jabatan'   => $item->siaga_jabatan->name,
                'shift'     => $item->shift->nama,
                'masuk'     => $item->mulaimasuk->format('H:i') .' - '. $item->selesaimasuk->format('H:i'),
                'pulang'    => $item->mulaipulang->format('H:i') .' - '. $item->selesaipulang->format('H:i')
            ];
        }

        return response()->json($out, Response::HTTP_OK);
    }

}
