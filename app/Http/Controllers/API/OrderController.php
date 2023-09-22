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
    
    public function getByIdCustomer($id)
    {
        $orders = Order::where('customer_id',$id)
            ->with('driver')->with('detailDriver')
            ->with('resto')->with('detailResto')
            ->with('customer')->with('detailCustomer')
            ->with('review')
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

    public function getByIdDriver($id)
    {
        $orders = Order::where('driver_id',$id)
            ->with('customer')->with('detailCustomer')
            ->with('resto')->with('detailResto')
            ->with('review')
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

    public function getDetailLogOrder($id)
    {
        $order = Order::where('id_order', $id)
            ->with('driver')->with('detailDriver')
            ->with('customer')->with('detailCustomer')
            ->with('resto')->with('detailResto')
            ->with('review')
            ->first();
        $totalJumlah = $order->total + $order->biaya_pesanan + $order->ongkos_kirim;

        if($order->produk_order != null){
            $idProduk = explode(',', $order->produk_order);
            $produk = OrderResto::whereIn('id_order_resto', $idProduk)->with('produk')->get();
            $response = [
                'status' => true,
                'message' => 'Berhasil',
                'order' => $order,
                'produk' => $produk,
                'totalJumlah' => $totalJumlah
            ];
            return response()->json($response, Response::HTTP_OK);
        }else{
            $response = [
                'status' => true,
                'message' => 'Berhasil',
                'order' => $order,
                'produk' => null,
                'totalJumlah' => $totalJumlah
            ];
            return response()->json($response, Response::HTTP_OK);
        }

    }
}

