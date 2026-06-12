<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Endpoint wilayah untuk dropdown cascading di form alamat (publik, read-only).
 * Mengembalikan {id, code, name} sehingga JS dapat menyetel hidden field nama
 * dari `data-name` atau langsung dari label option.
 */
class WilayahController extends Controller
{
    public function provinces()
    {
        return response()->json(
            DB::table('provinces')
                ->select('id', 'code', 'name')->orderBy('name')->get()
        );
    }

    public function regencies(Request $request)
    {
        $provinceId = (string) $request->query('province_id', '');
        if ($provinceId === '') {
            return response()->json([]);
        }
        return response()->json(
            DB::table('regencies')
                ->where('province_id', $provinceId)
                ->select('id', 'code', 'name')->orderBy('name')->get()
        );
    }

    public function districts(Request $request)
    {
        $regencyId = (string) $request->query('regency_id', '');
        if ($regencyId === '') {
            return response()->json([]);
        }
        return response()->json(
            DB::table('districts')
                ->where('regency_id', $regencyId)
                ->select('id', 'code', 'name')->orderBy('name')->get()
        );
    }
}
