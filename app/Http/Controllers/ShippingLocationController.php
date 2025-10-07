<?php

namespace App\Http\Controllers;

use Creasi\Nusa\Models\District;
use Creasi\Nusa\Models\Province;
use Creasi\Nusa\Models\Regency;
use Creasi\Nusa\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingLocationController extends Controller
{
    public function regencies(Request $request): JsonResponse
    {
        $provinceCode = $request->query('province');
        if (! $provinceCode) {
            return response()->json(['status' => 'error', 'message' => 'Kode provinsi wajib diisi.'], 422);
        }

        $province = Province::find($provinceCode);
        if (! $province) {
            return response()->json(['status' => 'error', 'message' => 'Provinsi tidak ditemukan.'], 404);
        }

        $regencies = $province->regencies()->orderBy('name')->get(['code', 'name']);

        return response()->json(['status' => 'ok', 'data' => $regencies]);
    }

    public function districts(Request $request): JsonResponse
    {
        $regencyCode = $request->query('regency');
        if (! $regencyCode) {
            return response()->json(['status' => 'error', 'message' => 'Kode kota/kabupaten wajib diisi.'], 422);
        }

        $regency = Regency::find($regencyCode);
        if (! $regency) {
            return response()->json(['status' => 'error', 'message' => 'Kota/kabupaten tidak ditemukan.'], 404);
        }

        $districts = $regency->districts()->orderBy('name')->get(['code', 'name']);

        return response()->json(['status' => 'ok', 'data' => $districts]);
    }

    public function villages(Request $request): JsonResponse
    {
        $districtCode = $request->query('district');
        if (! $districtCode) {
            return response()->json(['status' => 'error', 'message' => 'Kode kecamatan wajib diisi.'], 422);
        }

        $district = District::find($districtCode);
        if (! $district) {
            return response()->json(['status' => 'error', 'message' => 'Kecamatan tidak ditemukan.'], 404);
        }

        $villages = $district->villages()->orderBy('name')->get(['code', 'name', 'postal_code']);

        return response()->json(['status' => 'ok', 'data' => $villages]);
    }

    public function village(Request $request, string $code): JsonResponse
    {
        $village = Village::find($code);
        if (! $village) {
            return response()->json(['status' => 'error', 'message' => 'Kelurahan tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'code' => $village->code,
                'name' => $village->name,
                'postal_code' => $village->postal_code,
            ],
        ]);
    }
}
