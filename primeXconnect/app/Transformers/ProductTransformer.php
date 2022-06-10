<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Product;

class ProductTransformer extends TransformerAbstract
{
    public function transform(Product $product, bool $includeStockSummary = false)
    {
        $transformedData = [
            'id' => (int) $product->id,
            'name'          => (string) $product->name,
            'description'   => (string) $product->description,
            'code' => (string) $product->code,
            'created_at' => $product->created_at ? $product->created_at->format("Y-m-d H:i:s") : "",
            'updated_at' => $product->updated_at ? $product->updated_at->format("Y-m-d H:i:s") : "",
        ];
        if ($includeStockSummary) {
            $transformedData['number_of_stock_on_hand'] = $product->numberOfStocksOnHand();
            $transformedData['number_of_stock_taken'] = $product->numberOfStocksTaken();
        }
        return $transformedData;
    }
}
