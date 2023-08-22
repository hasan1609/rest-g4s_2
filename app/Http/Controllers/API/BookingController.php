<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Notifications\DriverNotification;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use LaravelFCM\Facades\FCM;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\OrderResto;
use App\Models\Cart;
use App\Models\Booking;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;


class BookingController extends Controller
{
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
        try {
            DB::beginTransaction();

            $inputIds = explode(',', $request->id);
            $item = Cart::whereIn('id_cart', $inputIds)->get();
            $totalJumlah = $item->sum('total');
            $orderItems = $this->saveOrderItems($item);
            // // Membuat booking sebelum memanggil findNearestDriver
            $booking = $this->createBooking($item, $inputIds, $totalJumlah);
            // cari driver
            $nearestDriver = $this->findNearestDriver($request->latitude, $request->longitude, $booking->id_booking);
    
            
            // $this->deleteCartItems($item);
    
            DB::commit();
    
            $response = [
                'status' => true,
                'message' => 'Order Berhasil Ditambah',
            ];
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
    
    public function createBooking($items, $inputIds, $totalJumlah)
    {
        $firstItem = $items->first();
        $key = $this->generateKeyFromDatetime();
        $subtotal = $totalJumlah + 3000 + 30000;
        return Booking::create([
            'id_booking' => $key,
            'customer_id' => $firstItem->user_id,
            'resto_id' => $firstItem->toko_id,
            'status' => "0",
            'produk_order' => implode(',', $inputIds),
            'ongkos_kirim' => '3000',
            'biaya_pesanan' => '3000',
            'total' => $subtotal,
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

    public function findNearestDriver($latitude, $longitude, $idBooking)
    {
        $database = Firebase::database();
        $driversRef = $database->getReference('driver_active');
        $driversSnapshot = $driversRef->getSnapshot();

        $nearestDriver = null;
        $maxDistance = 3; // Jarak maksimum dalam kilometer
        if ($driversSnapshot->exists()) {
            foreach ($driversSnapshot->getValue() as $driverId => $driverData) {
                $distance = $this->haversineDistance($latitude,$longitude, $driverData['latitude'], $driverData['longitude']);

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
            if ($nearestDriver) {
                $driverData = $nearestDriver['driver_id'];
                $distance = "5";
                $driver = User::find($driverData);
                $driver->notify(new DriverNotification($driverData, $distance, $idBooking));
                // $driver->notify(new DriverNotification($driverData, $distance));
                // Tampilkan data driver terdekat
                return response()->json(['data' => $nearestDriver]);
            } else {
                // Tidak ada driver yang ditemukan
                return response()->json(['data' => 'No drivers available'], 404);
            }
        } else {
            return response()->json(['message' => 'No active drivers found'], 404);
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
}
