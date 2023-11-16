<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DetailCustomer;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'tlp' => 'required|numeric|digits_between:10,13',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
    
        if ($validator->fails()) {
            return $this->handleError($validator->errors());
        }
        DB::beginTransaction();
        try {
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'tlp' => $request->tlp,
                'password' => password_hash($request->password, PASSWORD_DEFAULT),
                'role' => 'customer'
            ]);
    
            if ($user) {
                $user->detailCustomer()->create([
                    'foto' => null, // Set foto to null initially
                ]);
    
                if ($image = $request->file('foto')) {
                    $filename = date('YmdHis') . "." . $image->getClientOriginalExtension();
                    $request->foto = '/public/images/customer/' . $filename;
                    $user->detailCustomer->foto = $request->foto; // Update foto value
                    $resize = Image::make($image)->resize(300, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $resize->save(public_path('images/customer/' . $filename));
                }
    
                if ($request->has('latitude') && $request->has('longitude') && $request->has('alamat')) {
                    $user->detailCustomer->latitude = $request->latitude; // Update latitude value
                    $user->detailCustomer->longitude = $request->longitude; // Update longitude value
                    $user->detailCustomer->alamat = $request->alamat;
                }
    
                $user->detailCustomer->save(); // Save the changes
    
                DB::commit();
    
                return $this->handleResponse('Data Ditambahkan', $user, Response::HTTP_CREATED);
            }
        } catch (QueryException $e) {
            DB::rollback();
            if (isset($filename) && file_exists(public_path('images/customer/' . $filename))) {
                unlink(public_path('images/customer/' . $filename));
            }
            return $this->handleError($e->errorInfo);
        }
    }
    
    public function getDetail($id)
    {
        $user = User::where('id_user', $id)
        ->with('detailCustomer')
        ->first();
        
        return $this->handleResponse('Data user', $user, Response::HTTP_OK);
    }
    
    public function updateData(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());
        }
        DB::beginTransaction();
        try {
            // UPLOAD IMAGE
            if ($image = $request->file('foto')) {
                $filename = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $request->foto = '/public/images/customer/' . $filename;
                $resize = Image::make($image)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $resize->save(public_path('images/customer/' . $filename));
    
                // Hapus foto lama jika ada
                $old_file = str_replace('/public/', '', $user->detailCustomer->foto);
                if (File::exists(public_path($old_file))) {
                    File::delete(public_path($old_file));
                }
            } else {
                // Jika tidak ada foto baru yang diunggah, gunakan foto lama
                $request->foto = $user->detailCustomer->foto;
            }

            // Update data pada model User
            $user->update([
                'nama' => $request->nama
                // Tambahkan data lain yang ingin diupdate pada model User
            ]);

            // Update data pada model DetailUser
            $user->detailCustomer->update([
                'alamat' => $request->alamat,
                'foto' => $request->foto,
            ]);

            DB::commit();
            $response = [
                'status' => true,
                'message' => 'Data Berhasi Diubah',
                'data' => $user
            ];
            return response()->json($response, Response::HTTP_OK);
        } catch (QueryException $e) {
            DB::rollback();
            if (isset($filename) && file_exists(public_path('images/customer/' . $filename))) {
                unlink(public_path('images/customer/' . $filename));
            }
            return $this->handleError($e->errorInfo);
        }
    }
    
}
