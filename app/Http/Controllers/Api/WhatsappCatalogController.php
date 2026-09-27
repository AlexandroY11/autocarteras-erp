<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * Endpoints de solo lectura para que la IA de n8n tenga contexto real
 * (nunca inventa un producto/ciudad que no existe) y arme el resumen de
 * confirmación con precio real. Gate: ability 'whatsapp:catalog:read'
 * (ver routes/api.php) — el token del bot no tiene ninguna otra ability.
 */
class WhatsappCatalogController extends Controller
{
    public function products(): JsonResponse
    {
        return response()->json([
            'products' => Product::where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'base_price', 'shipping_price']),
        ]);
    }

    public function locations(): JsonResponse
    {
        return response()->json([
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'cities'      => City::orderBy('name')->get(['id', 'name', 'department_id']),
        ]);
    }
}
