<?php

namespace App\Http\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Transformers\ProductTransformer;
use App\Http\Api\Requests\ProductRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductController extends Controller
{
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function get(ProductRequest $request)
    {
        try {
            $productTransformed = (new ProductTransformer())
                ->transform($this->productService->get($request->id), (bool) $request->stock);
        } catch (Exception $e) {
            $errorCode = $e->getCode();
            if (!is_numeric($errorCode)) {
                $errorCode = 500;
            }
            return response()->json(['result' => 0, 'message' => $e->getMessage()], $errorCode);
        }
        return response()->json([
            'result' => 1,
            'data' => $productTransformed
        ], 200);
    }

    public function update(ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->productService->update(
                $request->id,
                [
                    'name' => $request->name,
                    'description' => $request->description,
                    'code' => $request->code
                ]
            );
            $newProductTransformed = (new ProductTransformer())
                ->transform($this->productService->get($request->id));
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
            'result' => 1,
            'data' => $newProductTransformed
        ], 200);
    }

    public function delete(ProductRequest $request)
    {
        try {
            $this->productService->delete($request->id);
        } catch (Exception $e) {
            $errorCode = $e->getCode();
            if (!is_numeric($errorCode)) {
                $errorCode = 500;
            }
            return response()->json(['result' => 0, 'message' => $e->getMessage()], $errorCode);
        }

        return response()->json(['result' => 1]);
    }
}
