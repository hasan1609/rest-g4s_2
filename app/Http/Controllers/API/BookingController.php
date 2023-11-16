<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Notifications\OrderNotification;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use LaravelFCM\Facades\FCM;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\DetailDriver;
use App\Models\OrderResto;
use App\Models\Cart;
use App\Models\Booking;
use App\Models\Order;
use App\Http\Controllers\API\NotificationController;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;

class BookingController extends Controller
{
    private $database;

    public function __construct()
    {
        $this->database = \App\Services\FirebaseService::connect();
    }
    public function generateKeyFromDatetime()
    {
        $datetime = now();
        $key = $datetime->format('YmdHis');
        $hash = Str::random(8);
        $finalKey = $key . $hash;
        return $finalKey;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            
            // Cari driver
            $kategoriAsli = $request->kategori;
            if($request->kategori == "resto"){
                $request['kategori'] = "motor_manual";
            }
            $nearestDriver = $this->findNearestDriver($request->latitude_dari, $request->longitude_dari, $request->kategori);
            
            if ($nearestDriver->original['data'] != null) {
                // Driver ditemukan, simpan data order
                $booking = $this->createBooking($request->user_id, $request->ongkir, $kategoriAsli, $request->alamat_dari, $request->latitude_dari, $request->longitude_dari, $request->alamat_tujuan, $request->latitude_tujuan, $request->longitude_tujuan);
    
                if ($kategoriAsli == "resto") {
                    $inputIds = explode(',', $request->id);
                    $item = Cart::whereIn('id_cart', $inputIds)->get();
                    $totalJumlah = $item->sum('total');
                    $orderItems = $this->saveOrderItems($item);
                    $booking->update([
                        'resto_id' => $item->first()->toko_id,
                        'produk_order' => implode(',', $inputIds),
                        'total' => $totalJumlah + $request->ongkir,
                    ]);
                    $this->deleteCartItems($item);
                } else {
                    $booking->update([
                        'total' => $request->ongkir,
                    ]);
                }
    
                // Simpan data driver pada booking
                $driverData = $nearestDriver->original['data'];
                $driverId = $driverData['driver_id'];
                $latDriver = $nearestDriver->original['data']['data']['latitude'];
                $longDriver = $nearestDriver->original['data']['data']['longitude'];
                $origin = $latDriver.",".$longDriver;
                $waypoints = $request->latitude_dari.",".$request->longitude_dari;
                $destination= $request->latitude_tujuan.",".$request->longitude_tujuan;
                $key = "AIzaSyDJYCwjovNkXpDiTgDi4G-jG5SlaZzOezs";
                $routes = $this->trackingOrder($origin, $destination, $waypoints, $key);
                $booking->update([
                    "driver_id" =>$driverId,
                    "routes" => $routes->original['data']
                ]);
    
                // Simpan data driver terkini
                $driver = DetailDriver::where('user_id', $driverId)->first();
            
                $driver->update([
                    "latitude" => $latDriver,
                    "longitude" => $longDriver,
                ]);
    
                // Buat dan kirim notifikasi
                $driver = User::find($driverId);
                $title = 'Pesanan Baru';
                $body = 'Kamu Mempunyai pesanan makanan baru. Kode: ' . $booking->id_order;
                $driver->notify(new OrderNotification($title, $body, $booking->id_order));
    
                // Ambil data order dengan relasi
                $order = Order::where('id_order', $booking->id_order)
                    ->with('resto')
                    ->with('detailResto')
                    ->with('driver')
                    ->with('detailDriver')->first();
    
                $response = [
                    'status' => true,
                    'data' => $order,
                ];
            } else {
                // Driver tidak ditemukan
                $response = [
                    'status' => false,
                    'data' => null,
                ];
            }
    
            DB::commit();
    
            return response()->json($response, Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function createBooking($user_id ,$ongkir , $kategori, $alamatDari, $latDari, $longDari,$alamatTujuan, $latTujuan, $longTujuan)
    {
        $key = $this->generateKeyFromDatetime();
        return Order::create([
            'id_order' => $key,
            'status' => "0",
            'ongkos_kirim' => $ongkir,
            'biaya_pesanan' => '3000',
            'total' => "0",
            'customer_id' => $user_id,
            'kategori' => $kategori,
            'alamat_dari'=> $alamatDari,
            'longitude_dari'=> $longDari,
            'latitude_dari' => $latDari,
            'alamat_tujuan'=> $alamatTujuan,
            'longitude_tujuan'=> $longTujuan,
            'latitude_tujuan' => $latTujuan
        ]);
    }

    private function saveOrderItems($items)
    {
        $orderItems = [];
        foreach ($items as $data) {
            $newData = [
                'id_order_resto' => $data->id_cart,
                'user_id' => $data->user_id,
                'produk_id' => $data->produk_id,
                'toko_id' => $data->toko_id,
                'jumlah' => $data->jumlah,
                'total' => $data->total,
                'catatan' => $data->catatan
            ];
            $order = OrderResto::create($newData);
            $orderItems[] = $order;
        }
        return $orderItems;
    }
    
    private function deleteCartItems($items)
    {
        foreach ($items as $data) {
            $data->delete();
        }
    }

    public function findNearestDriver($latitude, $longitude, $type)
    {
        $driversRef = $this->database->getReference('driver_active');
        $driversSnapshot = $driversRef->getSnapshot();

        $nearestDriver = null;
        $maxDistance = 3; // Jarak maksimum dalam kilometer
        if ($driversSnapshot->exists()) {
            foreach ($driversSnapshot->getValue() as $driverId => $driverData) {
                // Periksa status "active" driver
                if ($driverData['status'] === 'active' && $driverData['type'] === $type) {
                    $distance = $this->haversineDistance($latitude, $longitude, $driverData['latitude'], $driverData['longitude']);
        
                    if ($distance <= $maxDistance) {
                        if ($nearestDriver === null || $distance < $nearestDriver['distance']) {
                            $nearestDriver = [
                                'driver_id' => $driverId,
                                'distance' => $distance,
                                'data' => $driverData,
                            ];
                        }
                    }
                }
            }
            if ($nearestDriver) {
                $this->updateDriverStatus($nearestDriver['driver_id'], "waiting");
                // simpan notif log
                // app(NotificationController::class)->store($title, $body, $bookingId, $firstItem->user_id, $driverData);

                // Update data driver_id pada tabel booking
                // $this->updateBookingDriver($idBooking, $driver->id);
                // Tampilkan data driver terdekat
                return response()->json(['data' => $nearestDriver]);
            } else {
                // Tidak ada driver yang ditemukan
                return response()->json(['data' => null], 404);
            }
        } else {
            return response()->json(['data' => null], 404);
        }
    }

    private static function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }
    
    private function updateDriverStatus($driverId, $status)
    {
        $driversRef = $this->database->getReference('driver_active');
        $driver = $driversRef->getChild($driverId);
        $driverData = $driversRef->getSnapshot();
    
        if ($driverData->exists()) {
            $response = $driver->update([
                'status' => $status,
            ]);
        }
    }

    public function getById($id)
    {
        $booking = Order::where('id_order', $id)->with('detailResto')->first();
        if (!$booking) {
            $response = [
                'status' => false,
                'message' => 'Booking not found',
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }
        
        // Lanjutkan dengan logika Anda
        $jarak = $this->haversineDistance($booking->latitude_dari, $booking->longitude_dari, $booking->latitude_tujuan, $booking->longitude_tujuan);
        $response = [
            'status' => true,
            'jarak' => round($jarak),
            'data' => $booking,
        ];
        return response()->json($response, Response::HTTP_OK);
    }

    private function updateBookingDriver($bookingId, $driverId)
    {
        Booking::where('id_booking', $bookingId)->update(['driver_id' => $driverId]);
    }
    public function terimaBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
    
        if ($booking) {
            $order = Order::create([
                'id_order' => $booking->id_booking,
                'customer_id' => $booking->customer_id,
                'resto_id' => $booking->resto_id,
                'driver_id' => $request->driver_id,
                'status' => "0",
                'produk_order' => $booking->produk_order,
                'ongkos_kirim' => $booking->ongkos_kirim,
                'biaya_pesanan' => $booking->biaya_pesanan,
                'total' => $booking->total,
                'kategori' => $booking->kategori,
                'alamat_dari'=> $booking->alamat_dari,
                'longitude_dari'=> $booking->longitude_dari,
                'latitude_dari' => $booking->latitude_dari,
                'alamat_tujuan'=> $booking->alamat_tujuan,
                'longitude_tujuan'=> $booking->longitude_tujuan,
                'latitude_tujuan' => $booking->latitude_tujuan
            ]);
            
            $resto = User::findOrFail($booking->resto_id);
            if ($resto) {
                $restoTitle = 'Pesanan Baru';
                $restoBody = 'Kamu mempunyai pesanan produk baru ' . $booking->id_booking;
                $resto->notify(new OrderNotification($restoTitle, $restoBody, $booking->id_booking));
                // simpan notif log
                app(NotificationController::class)->store($restoTitle, $restoBody, $booking->id_booking, $request->driver_id, $booking->resto_id);
            }
            
            $customer = User::findOrFail($booking->customer_id);
            if ($customer) {
                $customerTitle = 'Pesanan Diterima';
                $customerBody = 'Pesanan (' . $booking->id_booking . ') kamu diterima. Driver sedang menuju ke Toko.';
                $customer->notify(new OrderNotification($customerTitle, $customerBody, $booking->id_booking));
                // simpan notif log
                app(NotificationController::class)->store($customerTitle, $customerBody, $booking->id_booking, $request->driver_id, $booking->customer_id);
                // hapus ppada booking
                $booking->delete();
            }

            $response = [
                'status' => true,
                'message' => 'Order Berhasil Diterima',
            ];
            return response()->json($response, Response::HTTP_CREATED);
        }
        
        return response()->json('Booking tidak ditemukan', [], Response::HTTP_NOT_FOUND);
    }
    
    public function cekBooking($id)
    {
        $order = Order::where('driver_id', $id)->whereIn('status', ["0"])
        ->with('resto')
        ->with('detailResto')
        ->with('driver')
        ->with('detailDriver')
        ->with('customer')
        ->with('detailCustomer')
        ->first();
        
        
        if ($order->count() > 0) {
            $hitungJarak = $this->haversineDistance($order->latitude_dari, $order->longitude_dari, $order->latitude_tujuan, $order->longitude_tujuan);
            $jarak = round($hitungJarak);
            // Jika ada
            $response = [
                'status' => true,
                'jarak' => $jarak,
                'data' => $order,
            ];
            return response()->json($response, Response::HTTP_OK);
        }else{
            $response = [
                'status' => false,
                'jarak' => $jarak,
                'data' => [],
            ];
            return response()->json($response, Response::HTTP_OK);
        }
    }

    public function trackingOrder($origin, $destination, $waypoints, $key)
    {
        $client = new Client();
        $routes = $client->get('https://maps.googleapis.com/maps/api/directions/json', [
            'query' => [
                'origin' => $origin,
                'destination' => $destination,
                'waypoints' => $waypoints,
                'key' => $key,
                'mode' => 'driving',
                'units' => 'imperial',
            ],
        ]);
        $data = json_decode($routes->getBody(), true);
        return response()->json(["data" => $data], Response::HTTP_CREATED);

    }
    
    public function updateStatus(Request $request)
    {
        $order = Order::where('id_order', $request->id)->first();
        $order->update([
            'status' => $request->status
        ]);
        if($request->status == "7"){
            $this->updateDriverStatus($request->driver_id, "busy");
        }
        if($request->status == "0")
        {
            if ($order->resto_id != null) {
                $restoTitle = 'Pesanan Baru';
                $restoBody = 'Kamu mempunyai pesanan produk baru ' . $order->id_order;
                $resto->notify(new OrderNotification($restoTitle, $restoBody, $order->id_order));
                // simpan notif log
                app(NotificationController::class)->store($restoTitle, $restoBody, $order->id_order, $request->driver_id, order->resto_id);
                
            }
            $customerTitle = 'Pesanan Diterima';
            $customerBody = 'Pesanan (' . $order->id_order . ') kamu diterima. Driver sedang menuju ke Toko.';
            $customer->notify(new OrderNotification($customerTitle, $customerBody, $order->id_order));
            // simpan notif log
            app(NotificationController::class)->store($customerTitle, $customerBody, $order->id_order, $request->driver_id, $order->customer_id);
        }
        
        $response = [
            'status' => true,
            'message' => 'Berhasil',
        ];
        return response()->json($response, Response::HTTP_OK);
    }
}
