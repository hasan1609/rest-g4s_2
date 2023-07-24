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
            'thn_kendaraan' => 'required',
            'tlp' => 'required|numeric|digits_between:10,13',
            'email' => 'required|email',
            'password' => 'required|min:8',
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
                'tlp' => $request->tlp,
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

    public function getMotor()
    {
        $driver = DetailDriver::where('status_driver', 'motor')->with('user')->get();
        return $this->handleResponse('Data Driver', $driver, Response::HTTP_OK);
    }

    public function getMobil()
    {
        $driver = DetailDriver::where('status_driver', 'mobil')->with('user')->get();
        return $this->handleResponse('Data Driver', $driver, Response::HTTP_OK);
    }

    public function getByIdDriver($id)
    {
        $driver = User::where('id_user', $id)->with('detailDriver')->first();
        return $this->handleResponse('Data Driver', $driver, Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes',
            'tempat_lahir' => 'sometimes',
            'ttl' => 'date|date_format:Y-m-d',
            'jk' => 'sometimes',
            'alamat' => 'sometimes',
            'foto' => 'image|mimes:jpeg,png,jpg|max:2048',
            'kendaraan' => 'sometimes',
            'status_driver' => 'sometimes',
            'plat_no' => 'sometimes',
            'thn_kendaraan' => 'digits:4',
            'tlp' => 'numeric|digits_between:10,13',
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());
        }

        DB::beginTransaction();
        try {
            // UPLOAD IMAGE
            if ($image = $request->file('foto')) {
                $filename = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $request->foto = '/public/images/driver/' . $filename;
                $resize = Image::make($image)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $resize->save(public_path('images/driver/' . $filename));
    
                // Hapus foto lama jika ada
                $old_file = str_replace('/public/', '', $user->detailDriver->foto);
                if (File::exists(public_path($old_file))) {
                    File::delete(public_path($old_file));
                }
            } else {
                // Jika tidak ada foto baru yang diunggah, gunakan foto lama
                $request->foto = $user->detailDriver->foto;
            }

            // Update data pada model User
            $user->update([
                'nama' => $request->nama,
                'tlp' => $request->tlp,
                // Tambahkan data lain yang ingin diupdate pada model User
            ]);

            // Update data pada model DetailUser
            $user->detailDriver->update([
                'tempat_lahir' => $request->tempat_lahir,
                'ttl' => $request->ttl,
                'jk' => $request->jk,
                'alamat' => $request->alamat,
                'foto' => $request->foto,
                'kendaraan' => $request->kendaraan,
                'status_driver' => $request->status_driver,
                'plat_no' => $request->plat_no,
                'thn_kendaraan' => $request->thn_kendaraan,
            ]);

            DB::commit();

            return $this->handleResponse('Data Berhasil Diubah', $user, Response::HTTP_OK);
        } catch (QueryException $e) {
            DB::rollback();
            if (isset($filename) && file_exists(public_path('images/driver/' . $filename))) {
                unlink(public_path('images/driver/' . $filename));
            }
            return $this->handleError($e->errorInfo);
        }
    }

    public function destroy($id)
    {
        // Temukan user berdasarkan ID
        $user = User::findOrFail($id);

        // Mulai transaksi database
        DB::beginTransaction();
        try {
            // Hapus foto driver jika ada
            $old_file = str_replace('/public/', '', $user->detailDriver->foto);
            if (File::exists(public_path($old_file))) {
                File::delete(public_path($old_file));
            }

            // Hapus detail driver dari user
            $user->detailDriver()->delete();

            // Hapus user (driver) dari database
            $user->delete();

            // Commit transaksi database
            DB::commit();

            return $this->handleResponse('Data Berhasil Dihapus', null, Response::HTTP_OK);
        } catch (QueryException $e) {
            // Jika terjadi kesalahan, rollback transaksi database
            DB::rollback();

            return $this->handleError($e->errorInfo);
        }
    }
}
