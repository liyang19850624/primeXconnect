<?php

namespace App\Http\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Http\Api\Requests\StocksRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class StocksController extends Controller
{
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function bulkCreate(StocksRequest $request)
    {
        try {
            DB::beginTransaction();
            $stocksToAdd = array_map(
                function ($row) {
                    return [
                        'on_hand' => $row['on_hand'] ?? 0,
                        'taken' => $row['taken'] ?? 0,
                        'production_date' => $row['production_date'] ?? date("Y-m-d H:i:s")
                    ];
                },
                $request->stocks
            );
            $this->productService->stockUp($request->product_id, $stocksToAdd);
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
