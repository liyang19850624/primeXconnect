<?php

namespace App\Http\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Api\Requests\ProductsRequest;
use App\Transformers\ProductTransformer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductsController extends Controller
{
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function list(ProductsRequest $request)
    {
        try {
            $transformer = new ProductTransformer();
            $products = $this->productService->list();
            $productsTransformed = (new Collection($products))
                ->transform(fn ($item) => $transformer->transform($item, (bool) $request->stock));
        } catch (Exception $e) {
            $errorCode = $e->getCode();
            if (!is_numeric($errorCode)) {
                $errorCode = 500;
            }
            return response()->json(['result' => 0, 'message' => $e->getMessage()], $errorCode);
        }
        return response()->json([
            'result' => 1,
            'data' => $productsTransformed
        ], 200);
    }

    public function bulkCreate(ProductsRequest $request)
    {
        try {
            DB::beginTransaction();
            $productsToAdd = array_map(
                function ($row) {
                    return [
                        'name' => $row['name'],
                        'code' => $row['code'],
                        'description' => $row['description'] ?? null
                    ];
                },
                $request->products
            );
            $this->productService->bulkCreate($productsToAdd);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorCode = $e->getCode();
            if (!is_numeric($errorCode)) {
                $errorCode = 500;
            }
            return response()->json(['result' => 0, 'message' => $e->getMessage()], $errorCode);
        }
        return response()->json([
            'result' => 1
        ], 200);
    }
}
