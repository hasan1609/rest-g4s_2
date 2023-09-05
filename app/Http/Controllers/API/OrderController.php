<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DetailDriver;
use App\Models\DetailResto;
use App\Models\DetailCustomer;
use App\Models\User;
use App\Models\Produk;
use App\Models\OrderResto;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{

    // status :
    // 0 = driver ke toko
    // 1 = driber sampai toko
    // 2 = driver mengantar
    // 3 = driver sampai
    // 4 = selesai
    // 5 = batal
    public function getByIdResto($id)
    {
        $orders = Order::where([
            ['resto_id','=',$id],
            ['status','!=', '5']
            ])
            ->with('driver')->with('detailDriver')
            ->with('customer')->with('detailCustomer')
            ->get();
            
        $idCountsPerOrder = [];

        foreach ($orders as $order) {
            $idCountsPerOrder[] = [
                'order' => $order,
                'count' => $order->countIds(),
            ];
        }
        $response = [
            'status' => true,
            'message' => 'Berhasil',
            'data' => $idCountsPerOrder
        ];

        return response()->json($response, Response::HTTP_OK);

    }
    
    public function getByIdUser($id)
    {
        $orders = Order::where('resto_id',$id)
            ->with('driver')->with('detailDriver')
            ->with('resto')->with('detailResto')
            ->get();
            
        $idCountsPerOrder = [];

        foreach ($orders as $order) {
            $idCountsPerOrder[] = [
                'order' => $order,
                'count' => $order->countIds(),
            ];
        }
        $response = [
            'status' => true,
            'message' => 'Berhasil',
            'data' => $idCountsPerOrder
        ];

        return response()->json($response, Response::HTTP_OK);

    }

    public function getProdukOrder($id)
    {
        $order = Order::where('id_order', $id)
            ->with('driver')->with('detailDriver')
            ->with('customer')->with('detailCustomer')
            ->first();
        $idProduk = explode(',', $order->produk_order);
        $produk = OrderResto::whereIn('id_order_resto', $idProduk)->with('produk')->get();
        $totalJumlah = $produk->sum('total');

        $response = [
            'status' => true,
            'message' => 'Berhasil',
            'order' => $order,
            'produk' => $produk,
            'totalJumlah' => $totalJumlah
        ];

        return response()->json($response, Response::HTTP_OK);
    }
}

