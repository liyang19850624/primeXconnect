<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Product;
use Tests\TestCase;

class StocksApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_create_new()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'product_id' => $product->id
        ];
        $numberOfStockToCreate = 10;
        for ($i = 0; $i < $numberOfStockToCreate; $i++) {
            $data['stocks'][] = [
                'on_hand' => $this->faker->numberBetween(1),
                'taken' => $this->faker->numberBetween(1),
                'production_date' => $this->faker->dateTimeBetween('-10 year', 'now')->format("Y-m-d H:i:s")
            ];
        }
        $response = $this->postJson('/api/stocks', $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'result'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        foreach ($data['stocks'] as $stock) {
            $this->assertDatabaseHas('stocks', [
                'product_id' => $product->id,
                'on_hand' => $stock['on_hand'],
                'taken' => $stock['taken'],
                'production_date' => $stock['production_date']
            ]);
        }
    }

    public function test_create_new_required_field_only()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'product_id' => $product->id
        ];
        $numberOfStockToCreate = 10;
        for ($i = 0; $i < $numberOfStockToCreate; $i++) {
            $data['stocks'][] = [];
        }
        $response = $this->postJson('/api/stocks', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        foreach ($data['stocks'] as $stock) {
            $this->assertDatabaseHas('stocks', [
                'product_id' => $product->id,
                'on_hand' => 0,
                'taken' => 0
            ]);
        }
    }

    public function test_create_new_load_test()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'product_id' => $product->id
        ];
        $data['stocks'] = array_fill(0, 20000, [
            'on_hand' => 10,
            'taken' => 10
        ]);
        $response = $this->postJson('/api/stocks', $data);

        var_dump($response->getContent());
        $response->assertStatus(200)
            ->assertJsonStructure([
                'result'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
    }

    public function test_create_new_failed_less_than_0()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'product_id' => $product->id
        ];
        $numberOfStockToCreate = 1;
        for ($i = 0; $i < $numberOfStockToCreate; $i++) {
            $data['stocks'][] = [
                'on_hand' => $this->faker->numberBetween(-20000, 0),
                'taken' => $this->faker->numberBetween(-20000, 0)
            ];
        }
        $response = $this->postJson('/api/stocks', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'stocks.0.on_hand',
                    'stocks.0.taken'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['need to have positive number of stock'], $responseContent['errors']['stocks.0.on_hand']);
        $this->assertEquals(['need to have positive number of stock'], $responseContent['errors']['stocks.0.taken']);
    }

    public function test_create_new_failed_no_stock()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'product_id' => $product->id
        ];
        $response = $this->postJson('/api/stocks', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'stocks'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['stocks is required'], $responseContent['errors']['stocks']);
    }

    public function test_create_new_failed_no_product_id()
    {
        $data = [];
        $numberOfStockToCreate = 10;
        for ($i = 0; $i < $numberOfStockToCreate; $i++) {
            $data['stocks'][] = [
                'on_hand' => $this->faker->numberBetween(1),
                'taken' => $this->faker->numberBetween(1)
            ];
        }
        $response = $this->postJson('/api/stocks', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'product_id'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['product_id is required'], $responseContent['errors']['product_id']);
    }

    public function test_create_new_failed_invalid_product()
    {
        $data = [
            'product_id' => $this->faker->randomNumber()
        ];
        $numberOfStockToCreate = 10;
        for ($i = 0; $i < $numberOfStockToCreate; $i++) {
            $data['stocks'][] = [
                'on_hand' => $this->faker->numberBetween(1),
                'taken' => $this->faker->numberBetween(1)
            ];
        }
        $response = $this->postJson('/api/stocks', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'product_id'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['Cannot find product'], $responseContent['errors']['product_id']);
    }
}
