<?php

namespace App\Modules\Products\Services;

use App\Models\Product;
use App\Modules\Products\DTOs\ProductDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Product::query()
            ->when(request('search'), fn($q, $s) =>
                $q->where('name', 'ilike', "%{$s}%")
            )
            ->when(request('active') !== null, fn($q) =>
                $q->where('active', filter_var(request('active'), FILTER_VALIDATE_BOOLEAN))
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(ProductDTO $dto): Product
    {
        return Product::create($dto->toArray());
    }

    public function update(Product $product, ProductDTO $dto): Product
    {
        $product->update($dto->toArray());
        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}