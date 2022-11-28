<?php

namespace Tests\Feature\API;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_return_product_categories_in_product_categories_nested_endpoint()
    {
        Product::factory()->hasCategories(5)->create();

        $response = $this->getJson('/api/products/1/categories');

        $response->assertStatus(200);

        $response->assertJson(fn(AssertableJson $json) =>
                            $json->hasAll([
                                'data.0.id',
                                'data.0.name',
                                'data.0.description',
                                'data.0.slug',
                                'data.0.created_at',
                                'data.0.updated_at'
                            ])
                            ->count('data', 5)->etc());
    }
}
