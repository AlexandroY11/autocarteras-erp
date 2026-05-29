<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Modules\Products\DTOs\ProductDTO;
use App\Modules\Products\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'pieces'              => 'nullable|integer|min:1',
            'avg_production_days' => 'nullable|integer|min:1',
            'base_price'          => 'required|numeric|min:0',
            'photo'               => 'nullable|string',
            'active'              => 'boolean',
        ]);

        $product = $this->service->create(ProductDTO::fromRequest($validated));

        return response()->json($product, 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name'                => 'sometimes|required|string|max:255',
            'description'         => 'nullable|string',
            'pieces'              => 'nullable|integer|min:1',
            'avg_production_days' => 'nullable|integer|min:1',
            'base_price'          => 'sometimes|required|numeric|min:0',
            'photo'               => 'nullable|string',
            'active'              => 'boolean',
        ]);

        $product = $this->service->update($product, ProductDTO::fromRequest($validated));

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->service->delete($product);
        return response()->json(null, 204);
    }
}