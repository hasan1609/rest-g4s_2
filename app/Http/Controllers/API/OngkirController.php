<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ongkir;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class OngkirController extends Controller
{
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

    public function getOngkirAll(Request $request)
    {
        $hitungJarak = $this->haversineDistance($request->lat1, $request->long1, $request->lat2, $request->long2);
        $jarak = round($hitungJarak);
        $ongkir = Ongkir::all();
        $result = [];
        
        foreach ($ongkir as $ongkirs) {
            $jenis_kendaraan = $ongkirs->nama;
            $harga = $ongkirs->ongkir;

            // Rata-rata kecepatan kendaraan dalam km per menit
            $rata_rata_kecepatan = $jenis_kendaraan == 'motor_otomatis' ? 0.2 : ($jenis_kendaraan == 'motor_manual' ? 0.2 : 0.1);
            // Estimasi waktu perjalanan dalam menit
            $estimasi_waktu = $jarak / $rata_rata_kecepatan;

            if ($jarak <= 5) {
                $totalHarga = $harga;
            } else {
                $additionalDistance = $jarak - 5;
                $additionalCost = $additionalDistance * ($jenis_kendaraan == 'motor_otomatis' ? 2000 : ($jenis_kendaraan == 'motor_manual' ? 2000 : 6000));
                $totalHarga = $harga + $additionalCost;
            }
            $result[] = [
                'jenis_kendaraan' => $jenis_kendaraan,
                'harga' => $totalHarga,
                'jarak' => $jarak,
                'waktu' => round($estimasi_waktu),
            ];
        }

        return $this->handleResponse('Data Ongkir', $result, Response::HTTP_OK);
    }
}
