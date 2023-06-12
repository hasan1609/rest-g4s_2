<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DetailDriver;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|numeric|digits_between:15,16',
            'nama' => 'required',
            'tempat_lahir' => 'required',
            'ttl' => 'date|date_format:Y-m-d',
            'jk' => 'required',
            'alamat' => 'required',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'kendaraan' => 'required',
            'status_driver' => 'required',
            'plat_no' => 'required',
            'thn_kendaraan' => 'required|digits:4',
            'tlp' => 'required|numeric|digits_between:10,13',
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
                // $path = 'image/driver';
                $filename = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $request->foto = '/public/images/driver/'.$filename;
            }
            
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => password_hash($request->password, PASSWORD_DEFAULT),
                'role' => 'driver'
            ]);

            if ($user) {
                $user->detailDriver()->create([
                    'nik' => $request->nik,
                    'tempat_lahir' => $request->tempat_lahir,
                    'ttl' => $request->ttl,
                    'jk' => $request->jk,
                    'alamat' => $request->alamat,
                    'foto' => $request->foto,
                    'kendaraan' => $request->kendaraan,
                    'status_driver' => $request->status_driver,
                    'plat_no' => $request->plat_no,
                    'thn_kendaraan' => $request->thn_kendaraan,
                    'tlp' => $request->tlp,
                ]);
                $resize = Image::make($image)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $resize->save(public_path('images/driver/' . $filename));
            }
            DB::commit();

            return $this->handleResponse('Data Ditambahkan', $user, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            DB::rollback();
            if (isset($filename) && file_exists(public_path('images/driver/' . $filename))) {
                unlink(public_path('images/driver/' . $filename));
            }
            return $this->handleError($e->errorInfo);
        }
    }
}
