<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Stock;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_get()
    {
        $product = Product::factory()->make();
        $product->save();

        $response = $this->get('/api/product/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'data' => [
                        'id',
                        'name',
                        'description',
                        'code',
                        'created_at',
                        'updated_at'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        $responseData = $responseContent['data'];
        $this->assertEquals($product->name, $responseData['name']);
        $this->assertEquals($product->description, $responseData['description']);
        $this->assertEquals($product->code, $responseData['code']);
        $this->assertEquals($product->created_at->format("Y-m-d H:i:s"), $responseData['created_at']);
        $this->assertEquals($product->updated_at->format("Y-m-d H:i:s"), $responseData['updated_at']);
    }

    public function test_get_stock_summary()
    {
        $product = Product::factory()->make();
        $product->save();
        $stockOne = Stock::factory(['product_id' => $product->id])->make();
        $stockOne->save();
        $stockTwo = Stock::factory(['product_id' => $product->id])->make();
        $stockTwo->save();

        $response = $this->get('/api/product/' . $product->id . '?stock=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'data' => [
                        'id',
                        'name',
                        'description',
                        'code',
                        'created_at',
                        'updated_at',
                        'number_of_stock_on_hand',
                        'number_of_stock_taken'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        $responseData = $responseContent['data'];
        $this->assertEquals($product->name, $responseData['name']);
        $this->assertEquals($product->description, $responseData['description']);
        $this->assertEquals($product->code, $responseData['code']);
        $this->assertEquals($product->created_at->format("Y-m-d H:i:s"), $responseData['created_at']);
        $this->assertEquals($product->updated_at->format("Y-m-d H:i:s"), $responseData['updated_at']);
        $this->assertEquals($stockOne->on_hand + $stockTwo->on_hand, $responseData['number_of_stock_on_hand']);
        $this->assertEquals($stockOne->taken + $stockTwo->taken, $responseData['number_of_stock_taken']);

    }

    public function test_get_fail_id_not_found()
    {
        $response = $this->get('/api/product/' . $this->faker->randomNumber());

        $response->assertStatus(422);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent["result"]);
        $this->assertEquals(["Cannot find product"], $responseContent["errors"]["id"]);
    }

    public function test_update()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'name' => $this->faker->word(),
            'description' => $this->faker->text(),
            'code' => $this->faker->word()
        ];

        $response = $this->patch('/api/product/' . $product->id, $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'data' => [
                        'id',
                        'name',
                        'description',
                        'code',
                        'created_at',
                        'updated_at'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        $responseData = $responseContent['data'];
        $this->assertEquals($data['name'], $responseData['name']);
        $this->assertEquals($data['description'], $responseData['description']);
        $this->assertEquals($data['code'], $responseData['code']);
        $this->assertEquals($product->created_at->format("Y-m-d H:i:s"), $responseData['created_at']);
        $this->assertNotEquals($product->updated_at->format("Y-m-d H:i:s"), $responseData['updated_at']);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $data['name'],
            'description' => $data['description'],
            'code' => $data['code']
        ]);
    }

    public function test_update_required_field_only()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'name' => $this->faker->word(),
            'code' => $this->faker->word()
        ];

        $response = $this->patch('/api/product/' . $product->id, $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'data' => [
                        'id',
                        'name',
                        'description',
                        'code',
                        'created_at',
                        'updated_at'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        $responseData = $responseContent['data'];
        $this->assertEquals($data['name'], $responseData['name']);
        $this->assertEquals('', $responseData['description']);
        $this->assertEquals($data['code'], $responseData['code']);
        $this->assertEquals($product->created_at->format("Y-m-d H:i:s"), $responseData['created_at']);
        $this->assertNotEquals($product->updated_at->format("Y-m-d H:i:s"), $responseData['updated_at']);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $data['name'],
            'description' => null,
            'code' => $data['code']
        ]);
    }

    public function test_update_optional_field_all_empty()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'name' => $this->faker->word(),
            'code' => $this->faker->word(),
            'description' => ''
        ];

        $response = $this->patch('/api/product/' . $product->id, $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result',
                'data' => [
                        'id',
                        'name',
                        'description',
                        'code',
                        'created_at',
                        'updated_at'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        $responseData = $responseContent['data'];
        $this->assertEquals($data['name'], $responseData['name']);
        $this->assertEquals('', $responseData['description']);
        $this->assertEquals($data['code'], $responseData['code']);
        $this->assertEquals($product->created_at->format("Y-m-d H:i:s"), $responseData['created_at']);
        $this->assertNotEquals($product->updated_at->format("Y-m-d H:i:s"), $responseData['updated_at']);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $data['name'],
            'description' => null,
            'code' => $data['code']
        ]);
    }


    public function test_update_fail_no_name()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'code' => $this->faker->word()
        ];

        $response = $this->patch('/api/product/' . $product->id, $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'name'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['name is required'], $responseContent['errors']['name']);
    }

    public function test_update_fail_no_code()
    {
        $product = Product::factory()->make();
        $product->save();

        $data = [
            'name' => $this->faker->word()
        ];

        $response = $this->patch('/api/product/' . $product->id, $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'code'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['code is required'], $responseContent['errors']['code']);
    }

    public function test_update_fail_id_not_found()
    {
        $data = [
            'name' => $this->faker->word(),
            'code' => $this->faker->word()
        ];

        $response = $this->patch('/api/product/' . $this->faker->randomNumber(), $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'id'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['Cannot find product'], $responseContent['errors']['id']);
    
    }

    public function test_delete()
    {
        $product = Product::factory()->make();
        $product->save();

        $response = $this->delete('/api/product/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(1, $responseContent['result']);
        $this->assertNotNull(Product::onlyTrashed()->find($product->id));
    }

    public function test_delete_fail_id_not_found()
    {
        $response = $this->delete('/api/product/' . $this->faker->randomNumber());

        $response->assertStatus(422)
            ->assertJsonStructure([
                'result',
                'errors' => [
                    'id'
                ]
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals(['Cannot find product'], $responseContent['errors']['id']);
    }

    public function test_delete_fail_soft_deleted()
    {
        $product = Product::factory(['deleted_at' => $this->faker->dateTimeThisMonth])->make();
        $product->save();

        $response = $this->delete('/api/product/' . $product->id);

        $response->assertStatus(404)
            ->assertJsonStructure([
                'result',
                'message'
            ]);

        $responseContent = $response->decodeResponseJson();

        $this->assertEquals(0, $responseContent['result']);
        $this->assertEquals('Cannot find product', $responseContent['message']);
    }
}
