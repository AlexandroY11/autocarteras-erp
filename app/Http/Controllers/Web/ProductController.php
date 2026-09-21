<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Modules\Products\DTOs\ProductDTO;
use App\Modules\Products\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function index()
    {
        $products = $this->service->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.form', ['product' => new Product()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pieces' => 'nullable|integer|min:1',
            'avg_production_days' => 'nullable|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);

        $this->service->create(ProductDTO::fromRequest($validated));

        return redirect('/products')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pieces' => 'nullable|integer|min:1',
            'avg_production_days' => 'nullable|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);

        $this->service->update($product, ProductDTO::fromRequest($validated));

        return redirect('/products')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        $this->service->delete($product);

        return redirect('/products')->with('success', 'Producto eliminado.');
    }

    public function show(Product $product)
    {
        return redirect('/products');
    }
}
