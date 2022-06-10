<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function list()
    {
        return Product::all();
    }

    public function get(int $id)
    {
        return Product::find($id);
    }

    public function bulkCreate(array $data)
    {
        foreach (array_chunk($data, 2000) as $chunkedData) {
            product::insert($chunkedData);
        }
    }

    public function update(Product $product, array $updatedFields)
    {
        $product->update($updatedFields);
    }

    public function delete(Product $product)
    {
        $product->delete();
    }
}
