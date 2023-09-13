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
            $booking = $this->createBooking($item, $inputIds, $totalJumlah, $request->alamat_dari, $request->latitude_dari, $request->longitude_dari, $request->alamat_tujuan, $request->latitude_tujuan, $request->longitude_tujuan);
            // cari driver
            $nearestDriver = $this->findNearestDriver($request->latitude, $request->longitude, $booking->id_booking, $item);
    
            
            $this->deleteCartItems($item);
    
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
    
    public function createBooking($items, $inputIds, $totalJumlah, $alamatDari, $latDari, $longDari,$alamatTujuan, $latTujuan, $longTujuan)
    {
        $firstItem = $items->first();
        $key = $this->generateKeyFromDatetime();
        $subtotal = $totalJumlah + 3000 + 30000;
        return Booking::create([
            'id_booking' => $key,
            'customer_id' => $firstItem->user_id,
            'resto_id' => $firstItem->toko_id,
            'produk_order' => implode(',', $inputIds),
            'ongkos_kirim' => '3000',
            'biaya_pesanan' => '3000',
            'total' => $subtotal,
            'kategori' => 'resto',
            'alamat_dari'=> $alamatDari,
            'longitude_dari'=> $longDari,
            'latitude_dari' => $latDari,
            'alamat_tujuan'=> $alamatTujuan,
            'longitude_tujuan'=> $longTujuan,
            'latitude_tujuan' => $latTujuan
        ]);
    }

    private function updateBookingDriver($bookingId, $driverId)
    {
        Booking::where('id_booking', $bookingId)->update(['driver_id' => $driverId]);
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

    public function findNearestDriver($latitude, $longitude, $bookingId, $items)
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
                $firstItem = $items->first();
                $driverData = $nearestDriver['driver_id'];
                $driver = User::find($driverData);
                $title = 'Pesanan Baru';
                $body = 'Kamu Mempunyai pesanan makanan baru. Kode: ' . $bookingId;

                $driver->notify(new OrderNotification($title, $body, $bookingId));
                // simpan notif log
                app(NotificationController::class)->store($title, $body, $bookingId, $firstItem->user_id, $driverData);

                // Update data driver_id pada tabel booking
                // $this->updateBookingDriver($idBooking, $driver->id);
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

    public function getById($id)
    {
        $booking = Booking::where('id_booking', $id)->with('detailResto')->first();
        if (!$booking) {
            $response = [
                'status' => false,
                'message' => 'Booking not found',
            ];
            return response()->json($response, Response::HTTP_NOT_FOUND);
        }
        
        // Lanjutkan dengan logika Anda
        $jarak = $this->haversineDistance($booking->detailResto->latitude, $booking->detailResto->longitude, $booking->latitude_tujuan, $booking->longitude_tujuan);
        $response = [
            'status' => true,
            'jarak' => round($jarak),
            'data' => $booking,
        ];
        return response()->json($response, Response::HTTP_OK);
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
}
