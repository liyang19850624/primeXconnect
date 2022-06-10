<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Product;
use App\Models\Stock;
use Tests\TestCase;

class ProductsApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_list_all()
    {
        $productOne = Product::factory()->make();
        $productOne->save();

        $productTwo = Product::factory()->make();
        $productTwo->save();

        $response = $this->get('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'code',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        $responseData = $responseContent['data'];
        $this->assertEquals($productOne->name, $responseData[0]['name']);
        $this->assertEquals($productOne->description, $responseData[0]['description']);
        $this->assertEquals($productOne->code, $responseData[0]['code']);
        $this->assertEquals($productOne->created_at->format("Y-m-d H:i:s"), $responseData[0]['created_at']);
        $this->assertEquals($productOne->updated_at->format("Y-m-d H:i:s"), $responseData[0]['updated_at']);

        $this->assertEquals($productTwo->name, $responseData[1]['name']);
        $this->assertEquals($productTwo->description, $responseData[1]['description']);
        $this->assertEquals($productTwo->code, $responseData[1]['code']);
        $this->assertEquals($productTwo->created_at->format("Y-m-d H:i:s"), $responseData[1]['created_at']);
        $this->assertEquals($productTwo->updated_at->format("Y-m-d H:i:s"), $responseData[1]['updated_at']);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_list_all_with_stock_summary()
    {
        $productOne = Product::factory()->make();
        $productOne->save();
        $productOneStockOne = Stock::factory(['product_id' => $productOne->id])->make();
        $productOneStockOne->save();
        $productOneStockTwo = Stock::factory(['product_id' => $productOne->id])->make();
        $productOneStockTwo->save();

        $productTwo = Product::factory()->make();
        $productTwo->save();
        $productTwoStockOne = Stock::factory(['product_id' => $productTwo->id])->make();
        $productTwoStockOne->save();
        $productTwoStockTwo = Stock::factory(['product_id' => $productTwo->id])->make();
        $productTwoStockTwo->save();

        $response = $this->get('/api/products?stock=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'code',
                        'created_at',
                        'updated_at',
                        'number_of_stock_on_hand',
                        'number_of_stock_taken'
                    ]
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        $responseData = $responseContent['data'];
        $this->assertEquals($productOne->name, $responseData[0]['name']);
        $this->assertEquals($productOne->description, $responseData[0]['description']);
        $this->assertEquals($productOne->code, $responseData[0]['code']);
        $this->assertEquals($productOne->created_at->format("Y-m-d H:i:s"), $responseData[0]['created_at']);
        $this->assertEquals($productOne->updated_at->format("Y-m-d H:i:s"), $responseData[0]['updated_at']);
        $this->assertEquals($productOneStockOne->on_hand + $productOneStockTwo->on_hand, $responseData[0]['number_of_stock_on_hand']);
        $this->assertEquals($productOneStockOne->taken + $productOneStockTwo->taken, $responseData[0]['number_of_stock_taken']);

        $this->assertEquals($productTwo->name, $responseData[1]['name']);
        $this->assertEquals($productTwo->description, $responseData[1]['description']);
        $this->assertEquals($productTwo->code, $responseData[1]['code']);
        $this->assertEquals($productTwo->created_at->format("Y-m-d H:i:s"), $responseData[1]['created_at']);
        $this->assertEquals($productTwo->updated_at->format("Y-m-d H:i:s"), $responseData[1]['updated_at']);
        $this->assertEquals($productTwoStockOne->on_hand + $productTwoStockTwo->on_hand, $responseData[1]['number_of_stock_on_hand']);
        $this->assertEquals($productTwoStockOne->taken + $productTwoStockTwo->taken, $responseData[1]['number_of_stock_taken']);
    }

    public function test_create_new()
    {
        $numberOfProductToCreate = 10;
        $data = [];
        for ($i = 0; $i < $numberOfProductToCreate; $i++) {
            $data['products'][] = [
                'name' => $this->faker->word(),
                'description' => $this->faker->text(),
                'code' => $this->faker->word()
            ];
        }
        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);

        foreach ($data['products'] as $product) {
            $this->assertDatabaseHas('products', [
                'name' => $product['name'],
                'description' => $product['description'],
                'code' => $product['code']
            ]);
        }
    }

    public function test_create_new_required_field_only()
    {
        $numberOfProductToCreate = 10;
        $data = [];
        for ($i = 0; $i < $numberOfProductToCreate; $i++) {
            $data['products'][] = [
                'name' => $this->faker->word(),
                'code' => $this->faker->word()
            ];
        }
        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);

        foreach ($data['products'] as $product) {
            $this->assertDatabaseHas('products', [
                'name' => $product['name'],
                'description' => null,
                'code' => $product['code']
            ]);
        }
    }

    public function test_create_new_optional_field_all_empty()
    {
        $numberOfProductToCreate = 10;
        $data = [];
        for ($i = 0; $i < $numberOfProductToCreate; $i++) {
            $data['products'][] = [
                'name' => $this->faker->word(),
                'code' => $this->faker->word(),
                'description' => ''
            ];
        }
        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);

        foreach ($data['products'] as $product) {
            $this->assertDatabaseHas('products', [
                'name' => $product['name'],
                'description' => null,
                'code' => $product['code']
            ]);
        }
    }

    public function test_create_new_load_test()
    {
        $data = [
            'products' => array_fill(0, 5000, [
                'name' => 'product',
                'description' => 'description',
                'code' => '123'
            ])
        ];
        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
    }


    public function test_create_new_failed_no_name()
    {
        $data['products'][] = [
            'code' => $this->faker->word(),
            'description' => $this->faker->text()
        ];
        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'products.0.name'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['name is required'], $responseContent['errors']['products.0.name']);
    }

    public function test_create_new_failed_no_code()
    {
        $data['products'][] = [
            'name' => $this->faker->word(),
            'description' => $this->faker->text()
        ];
        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'products.0.code'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['code is required'], $responseContent['errors']['products.0.code']);
    }
}
