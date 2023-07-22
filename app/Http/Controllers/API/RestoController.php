<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DetailResto;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RestoController extends Controller
{
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|numeric|digits_between:15,16',
            'nama' => 'required',
            'tlp' => 'required|numeric|digits_between:10,13',
            'tempat_lahir' => 'required',
            'ttl' => 'date|date_format:Y-m-d',
            'alamat' => 'required',
            "jam_buka" => 'date_format:H:i',
            "jam_tutup" => 'date_format:H:i',
            'nama_resto' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());
        }
        DB::beginTransaction();
        try {
            // UPLOAD IMAGE
            if ($image = $request->file('foto')) {
                // $path = 'image/resto';
                $filename = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $request->foto = '/public/images/resto/'.$filename;
            }
            
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'tlp' => $request->tlp,
                'password' => password_hash($request->password, PASSWORD_DEFAULT),
                'role' => 'resto'
            ]);
            if ($user) {
                $user->detailResto()->create([
                    'nik' => $request->nik,
                    'tempat_lahir' => $request->tempat_lahir,
                    'ttl' => $request->ttl,
                    'alamat' => $request->alamat,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'nama_resto' => $request->nama_resto,
                    'status_toko' => "tutup",
                    'status_akun' => "proses",
                    'foto' => null,
                ]);
                if ($image = $request->file('foto')) {
                    $filename = date('YmdHis') . "." . $image->getClientOriginalExtension();
                    $request->foto = '/public/images/resto/' . $filename;
                    $user->detailResto->foto = $request->foto; // Update foto value
                    $resize = Image::make($image)->resize(300, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $resize->save(public_path('images/resto/' . $filename));
                }
                $user->detailResto->save();
            }
            DB::commit();

            return $this->handleResponse('Data Ditambahkan', $user, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            DB::rollback();
            if (isset($filename) && file_exists(public_path('images/resto/' . $filename))) {
                unlink(public_path('images/resto/' . $filename));
            }
            return $this->handleError($e->errorInfo);
        }
    }

    // get status toko
    public function getStatusResto($id)
    {
        //
        $status = DetailResto::where('user_id',$id)->first();
        try {
            return $this->handleResponse('Berhasil', ['status'=> $status->status_toko] , Response::HTTP_OK);
        } catch (QueryException $e) {
            return $this->handleError($e->errorInfo);
        }
    }

    // update status resyo
    public function updateStatusResto(Request $request, $id)
    {
        $resto = DetailResto::where('user_id',$id)->first();
        $param = $request->param;

        if ($param == "buka") {
            $input['status_toko'] = "buka";
            $resto->update($input);
            return $this->handleResponse('Berhasil', ['status' => 'buka'] , Response::HTTP_OK);
        }
        if ($param == "tutup") {
            $input['status_toko'] = "tutup";
            $resto->update($input);
            return $this->handleResponse('Berhasil', ['status' => 'tutup'], Response::HTTP_OK);
        }
    }

    // get toko by id
    public function show($id)
    {
        //
        $resto = User::with('detailResto')->findOrFail($id);
        try {
            return $this->handleResponse('Data Resto', $resto, Response::HTTP_OK);
        } catch (QueryException $e) {
            return $this->handleError($e->errorInfo);
        }
    }

    // update by id
    public function update(Request $request, $id)
    {
        $resto = DetailResto::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'nama_resto' => 'required',
            'tlp' => 'required|numeric|digits_between:10,13',
            'alamat' => 'required',
            "jam_buka" => 'date_format:H:i',
            "jam_tutup" => 'date_format:H:i',
            'foto' => 'image|mimes:jpeg,png,jpg|max:5048',
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());
        }
        DB::beginTransaction();
        try {
            $input = $request->all();
            $old_file = str_replace('/public/', '', $resto->foto);
            $image = $request->file('foto');
            if ($image != null) {
                $filename = date('YmdHis') . "." . $request->foto->getClientOriginalExtension();
                $input['foto'] = '/public/images/resto/'.$filename;
                $resize = Image::make($image)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                if (File::exists($old_file)) {
                    File::delete($old_file);
                    $resize->save(public_path('images/resto/' . $filename));
                }
            }
            $resto->update($input);
            DB::commit();
            $response = [
                'status' => true,
                'message' => 'Data Berhasi Diubah',
            ];
            return response()->json(["data" => $resto], Response::HTTP_OK);
        } catch (QueryException $e) {
            DB::rollback();
            if (isset($filename) && file_exists(public_path('images/resto/' . $filename))) {
                unlink(public_path('images/resto/' . $filename));
            }
            return $this->handleError($e->errorInfo);
        }
    }

    // get resto terdekat
    public function restoTerdekat($lat, $long)
    {
        // $resto = DB::table('detail_restos')
        //     ->select("detail_restos.*", DB::raw("(((acos(sin((" . $lat . "*pi()/180)) * sin((`latitude`*pi()/180)) + cos((" . $lat . "*pi()/180)) * cos((`latitude`*pi()/180)) * cos(((" . $long . "- `longitude`) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance
        // "))
        //     ->having('distance', '<', 5)
        //     ->get();

        $resto = DetailResto::select('*', DB::raw('ROUND((6371 * acos(cos(radians(' . $lat . ')) * cos(radians(latitude)) * cos(radians(longitude) - radians(' . $long . ')) + sin(radians(' . $lat . ')) * sin(radians(latitude)))), 2) AS distance'))
            ->having('distance', '<', 5)
            ->with('user')
            ->with('produk')
            ->get();

        if (!$resto) {
            return $this->handleError('Tidak Resto Terdekat', [], Response::HTTP_NOT_FOUND);
        } else
            return $this->handleResponse('Data Resto Terdekat', $resto, Response::HTTP_OK);
    }
}
