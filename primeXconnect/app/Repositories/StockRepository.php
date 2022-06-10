<?php

namespace App\Repositories;

use App\Models\Stock;

class StockRepository
{
    public function createBulk(array $data)
    {
        if (empty($data)) {
            return;
        }
        foreach (array_chunk($data, 2000) as $chunkedData) {
            Stock::insert($chunkedData);
        }
    }
}
