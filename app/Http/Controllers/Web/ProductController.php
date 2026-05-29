<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->when(request('search'), fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%")
            )
            ->orderBy('name')
            ->paginate(20);

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
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);

        Product::create($validated);

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
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);

        $product->update($validated);

        return redirect('/products')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect('/products')->with('success', 'Producto eliminado.');
    }

    public function show(Product $product)
    {
        return redirect('/products');
    }
}
