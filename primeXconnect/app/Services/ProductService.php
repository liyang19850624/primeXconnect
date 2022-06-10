<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Repositories\StockRepository;
use Exception;

class ProductService
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository, StockRepository $stockRepository)
    {
        $this->productRepository = $productRepository;
        $this->stockRepository = $stockRepository;
    }

    public function list()
    {
        return $this->productRepository->list();
    }

    public function get(int $id)
    {
        return $this->productRepository->get($id);
    }

    public function bulkCreate(array $data)
    {
        return $this->productRepository->bulkCreate($data);
    }

    public function update(int $id, array $data)
    {
        $product = $this->productRepository->get($id);
        if (!$product) {
            throw new Exception("Cannot find product", 404);
        }
        $this->productRepository->update(
            $product,
            $data
        );
    }

    public function delete(int $id)
    {
        $product = $this->productRepository->get($id);
        if (!$product) {
            throw new Exception("Cannot find product", 404);
        }
        $this->productRepository->delete($product);
    }

    public function stockUp(int $id, array $data)
    {
        $product = $this->get($id);
        if (!$product) {
            throw new Exception("Cannot find resource", 404);
        }
        $data = array_map(function ($row) use ($id) {
            $row['product_id'] = $id;
            return $row;
        }, $data);
        $this->stockRepository->createBulk($data);
    }
}
