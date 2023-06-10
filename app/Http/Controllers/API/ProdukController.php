<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Intervention\Image\Facades\Image;

class ProdukController extends Controller
{
    // GET Produk
    public function index()
    {
        $produk = Produk::with('user')
            ->orderBy('user_id')
            ->get();
        return $this->handleResponse('Data Produk', $produk, Response::HTTP_OK);
    }

    // GET KATEGORY PRODUK
    public function getByKatProduk($id, $kategori)
    {
        $produk = Produk::where('user_id', $id)
            ->where('kategori', $kategori)
            ->get();
        return $this->handleResponse('Data Produk', $produk, Response::HTTP_OK);
    }

    public function getCount($id)
    {
        $count = DB::table('produks')
            ->select('kategori', DB::raw("COUNT(*) as hasil"))
            ->where('user_id', '=', $id)
            ->groupBy('kategori')
            ->get();
        // ->count();
        return $this->handleResponse('Data Produk', $count, Response::HTTP_OK);
    }

    // tambah data produk
    public function store(Request $request,)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'nama_produk' => 'required',
            'harga' => 'required',
            'kategori' => 'required',
            'foto_produk' => 'required|image|mimes:jpeg,png,jpg|max:5048'
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());
        }

        DB::beginTransaction();
        try {
            $input = $request->all();
            // UPLOAD IMAGE
            if ($image = $request->file('foto_produk')) {
                // $path = 'image/produk';
                $filename = date('YmdHis') . "." . $image->getClientOriginalExtension();
                $input['foto_produk'] = '/public/images/produk/'.$filename;
            }
            $produk = Produk::create($input);
            if ($produk) {
                $resize = Image::make($image)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $resize->save(public_path('images/produk/' . $filename));
            }
            $response = [
                'status' => true,
                'message' => 'Produk Berhasi Ditambah',
            ];
            DB::commit();
            return response()->json($response, Response::HTTP_CREATED);

        } catch (QueryException $e) {
            DB::rollback();
            if (isset($filename) && file_exists(public_path('images/produk/' . $filename))) {
                unlink(public_path('images/produk/' . $filename));
            }
            return $this->handleError($e->errorInfo);
        }
    }

    // ubah data produk
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kategori' => 'required',
                'foto_produk' => 'image|mimes:jpeg,png,jpg|max:5048'
            ]);

            if ($validator->fails()) {
                return $this->handleError($validator->errors());
            }
            // UPLOAD IMAGE
            $produk = Produk::findOrFail($id);
            $input = $request->all();
            $old_file = str_replace('/public/', '', $produk->foto_produk);
            $image = $request->File('foto_produk');
            if ($image != null) {
                $filename = date('YmdHis') . "." . $request->foto_produk->getClientOriginalExtension();
                $input['foto_produk'] = '/public/images/produk/'.$filename;   
                $resize = Image::make($image)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                if (File::exists($old_file)) {
                    File::delete($old_file);
                    $resize->save(public_path('images/produk/' . $filename));
                }
            }
            $produk->update($input);
            $response = [
                'status' => true,
                'message' => 'Produk Berhasi Diubah',
            ];
            return response()->json($response, Response::HTTP_OK);
        } catch (QueryException $e) {
            return $this->handleError($e->errorInfo);
        }
    }

    // hapus produk
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        try {
            $produk->delete();
            $string = str_replace('/public/', '', $produk->foto_produk);
            $file = public_path($string);
            File::delete($file);
            $response = [
                'status' => true,
                'message' => 'Produk Berhasi Dihapus',
            ];
            return response()->json($response, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return $this->handleError($e->errorInfo);
        }
    }

    // ubah status produk
    public function updateStatusProduk(Request $request, $id)
    {
        $makanan = Produk::findOrFail($id);
        $param = $request->param;

        if ($param == 'tersedia') {
            $input['status'] = "tersedia";
            $makanan->update($input);
            return $this->handleResponse('Berhasil', ['status' => 'tersedia'] , Response::HTTP_OK);
        }
        if ($param == 'habis') {
            $input['status'] = "habis";
            $makanan->update($input);
            return $this->handleResponse('Berhasil', ['status' => 'habis'], Response::HTTP_OK);
        }
    }
}
