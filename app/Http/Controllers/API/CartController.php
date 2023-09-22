<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CartController extends Controller
{
    public function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $distance = round((6371 * acos(cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)) + sin(deg2rad($lat1)) * sin(deg2rad($lat2)))), 2);
        return $distance;
    }

    public function index($id, $lat, $long)
    {
        $count = Cart::select('user_id', 'toko_id', DB::raw('COUNT(produk_id) as jumlah_produk'))
        ->where('user_id', $id)
        ->with('resto')
        ->groupBy('user_id', 'toko_id')
        ->get();

        $result = [];

        foreach ($count as $item) {
            $resto = $item->resto;
            $distance = $this->haversineDistance($lat, $long, $resto->latitude, $resto->longitude);
            $item->resto->distance = $distance;
            $result[] = $item;
        }

        return $this->handleResponse('Data Cart', $result, Response::HTTP_OK);
    }

    public function getCount($id)
    {
        $user = Cart::where('user_id', $id)->count('toko_id');
        return $this->handleResponse('Data Cart', $user, Response::HTTP_OK);
    }
    

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'produk_id'=> 'required',
            'toko_id'=> 'required',
            'jumlah'=> 'required',
            'harga'=> 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $input = $request->all();
            $input['total'] = $request->jumlah * $request->harga;

            $existingCart = Cart::where('produk_id', $request->produk_id)
                ->where('user_id', $request->user_id)
                ->first();

            if ($existingCart) {
                // Update jumlah dan total jika produk_id dan user_id sudah ada di keranjang
                $existingCart->jumlah = $request->jumlah;
                $existingCart->total = $input['total'];
                $existingCart->catatan = $input['catatan'];
                $existingCart->save();
            } else {
                // Tambahkan produk baru ke keranjang
                $produk = Cart::create($input);
            }

            DB::commit();
            $response = [
                'status' => true,
                'message' => 'Produk Berhasil Ditambah',
            ];
            return response()->json($response, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->errorInfo
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
    public function show($id, $user)
    {
        $cart = Cart::where('user_id', $user)->where('toko_id', $id)->with('resto')->with('produk')->get();
        $totalJumlah = $cart->sum('total');

        $response = [
            'status' => true,
            'message' => 'Detail Cart',
            'totalJumlah' => $totalJumlah,
            'data' => $cart,
        ];

        return response()->json($response, Response::HTTP_OK);
    }



    // hapus cart by id toko
    public function destroy($id, $user)
    {
        try {
            Cart::where('user_id', $user)->where('toko_id',$id)->delete();
            $response = [
                'status' => true,
                'message' => 'Keranjang Berhasi Dihapus',
            ];
            return response()->json($response, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return $this->handleError($e->errorInfo);
        }
    }

    // hapus cart by id cart
    public function destroyItem($id)
    {
        try {
            Cart::where('id_cart',$id)->delete();
            $response = [
                'status' => true,
                'message' => 'Keranjang Berhasi Dihapus',
            ];
            return response()->json($response, Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return $this->handleError($e->errorInfo);
        }
    }
}
