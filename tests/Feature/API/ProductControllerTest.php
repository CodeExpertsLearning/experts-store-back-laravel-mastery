<?php

namespace Tests\Feature\API;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_product_get_endpoint_list_all_products()
    {
        $token = $this->makeUserToken();
        Product::factory(3)->create();

        $response = $this->getJson('/api/products', ['Authorization' => 'Bearer ' . $token]);

        $response
            ->assertStatus(200);

        $response->assertJson(function(AssertableJson $json) {
            $json->hasAll(['data', 'meta', 'links']);
            $json->hasAll(['data.0.id', 'data.0.name', 'data.0.price', 'data.0.price_float']);
            $json->whereAllType([
                'data.0.id' => 'integer',
                'data.0.name' => 'string',
                'data.0.price' => 'integer',
                'data.0.price_float' => 'double'
            ]);

            $json->count('data', 3)->etc();
        });
    }

    public function test_should_product_get_endpoint_list_all_products_paginated()
    {
        $token = $this->makeUserToken();
        Product::factory(20)->create();

        $response = $this->getJson('/api/products', ['Authorization' => 'Bearer ' . $token]);

        $response
            ->assertStatus(200);

        $response->assertJson(fn(AssertableJson $json) =>
                        $json->where('data.0.id', 1)
                            ->where('data.9.id', 10)
                            ->count('data', 10)->etc());

        $response = $this->getJson('/api/products?page=2');
        $response
            ->assertStatus(200);

        $response->assertJson(fn(AssertableJson $json) =>
                    $json->where('data.0.id', 11)
                         ->where('data.9.id', 20)
                         ->count('data', 10)->etc());
    }

    public function test_should_product_get_endpoint_returns_a_single_product()
    {
        $token = $this->makeUserToken();
        Product::factory(1)->create(['name' => 'Produto 1', 'price' => 3999]);

        $response = $this->getJson('/api/products/1', ['Authorization' => 'Bearer ' . $token]);

        $response
            ->assertStatus(200);

        $response->assertJson(fn(AssertableJson $json) =>
            $json->has('data')
                ->hasAll(['data.id', 'data.name', 'data.price', 'data.price_float'])
                ->whereAllType([
                    'data.id' => 'integer',
                    'data.name' => 'string',
                    'data.price' => 'integer',
                    'data.price_float' => 'double'
                ])
                ->whereAll([
                    'data.name' => 'Produto 1',
                    'data.price' => 3999,
                    'data.price_float' => 39.99
                ])
        );
    }

    public function test_should_product_post_endpoint_throw_an_unauthorized_status()
    {
        $response = $this->postJson('/api/products', []);
        $response->assertUnauthorized();
    }

    public function test_should_validate_payload_data_when_create_a_new_product()
    {
        $token = $this->makeUserToken();

        $response = $this->postJson('/api/products', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertUnprocessable();

        $response->assertJson(fn(AssertableJson $json) =>
            $json->hasAll(['message', 'errors'])
                ->hasAll(['errors.name', 'errors.price'])
                ->whereAll([
                    'errors.name.0' => 'Campo obrigatório!',
                    'errors.price.0' => 'Campo obrigatório!'
                ])
        );
    }

    public function test_should_product_post_endpoint_create_a_new_product()
    {
        $product = [
            'name' => 'Produto Teste',
            'description' => 'Descrição teste',
            'price' => 3999
        ];

       $token = $this->makeUserToken();

        $response = $this->postJson('/api/products', $product, ['Authorization' => 'Bearer ' . $token]);

        $response->assertCreated();

        $response->assertJson(fn(AssertableJson $json) =>
        $json->has('data')
            ->hasAll(['data.id', 'data.name', 'data.price', 'data.price_float'])
            ->whereAllType([
                'data.id' => 'integer',
                'data.name' => 'string',
                //'data.description' => 'string|null',
                'data.price' => 'integer',
                'data.price_float' => 'double'
            ])
            ->whereAll([
                'data.name' => 'Produto Teste',
                'data.price' => 3999,
                'data.price_float' => 39.99
            ])
        );
    }

    public function test_should_product_put_endpoint_throw_an_unauthorized_status()
    {
        Product::factory()->create(['name' => 'Produto Put', 'price' => 1999]);

        $response = $this->putJson('/api/products/1', []);
        $response->assertUnauthorized();
    }

    public function test_should_validate_payload_data_when_update_a_product()
    {
       $token = $this->makeUserToken();

        Product::factory()->create(['name' => 'Produto Put', 'price' => 1999]);

        $response = $this->putJson('/api/products/1', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertUnprocessable();

        $response->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['message', 'errors'])
            ->hasAll(['errors.name', 'errors.price'])
            ->whereAll([
                'errors.name.0' => 'Campo obrigatório!',
                'errors.price.0' => 'Campo obrigatório!'
            ])
        );
    }

    public function test_should_product_put_endpoint_update_a_product()
    {
        Product::factory()->create(['name' => 'Produto Put', 'price' => 1999]);

        $productUpdateData = [
            'name' => 'Produto Put Update',
            'price' => 1999
        ];

       $token = $this->makeUserToken();

        $response = $this->putJson('/api/products/1', $productUpdateData, ['Authorization' => 'Bearer ' . $token]);

        $response->assertOk();

        $response->assertJson(fn(AssertableJson $json) =>
        $json->has('data')
            ->hasAll(['data.id', 'data.name', 'data.price', 'data.price_float'])
            ->whereAllType([
                'data.id' => 'integer',
                'data.name' => 'string',
                //'data.description' => 'string|null',
                'data.price' => 'integer',
                'data.price_float' => 'double'
            ])
            ->whereAll([
                'data.name' => 'Produto Put Update',
                'data.price' => 1999,
                'data.price_float' => 19.99
            ])
        );
    }

    public function test_should_product_delete_endpoint_throw_an_unauthorized_status()
    {
        Product::factory()->create(['name' => 'Produto Put', 'price' => 1999]);

        $response = $this->deleteJson('/api/products/1', []);
        $response->assertUnauthorized();
    }

    public function test_should_product_delete_endpoint_remove_a_product()
    {
        Product::factory()->create(['name' => 'Produto Delete', 'price' => 1999]);

       $token = $this->makeUserToken();

        $response = $this->deleteJson('/api/products/1', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertNoContent();

        $response = $this->getJson('/api/product/1');
        $response->assertNotFound();
    }
}
