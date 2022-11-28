<?php

namespace Tests\Feature\API;

use App\Models\Product;
use App\Models\ProductPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductPhotosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_post_product_photos_endpoint_should_save_one_photo_by_upload()
    {
        $token = $this->makeUserToken();

        $product = Product::factory()->create();

        $image = UploadedFile::fake()->image('produto-foto.jpg');

        $response = $this->post('/api/products/1/photos',
            [
            'photos' => [
                $image
                ]
            ],
            [
                'Content-Type' => 'application/form-data',
                'Authorization' => 'Bearer ' . $token
            ]
        );

        Storage::disk('public')->assertExists('products/' . $image->hashName());

        $this->assertEquals('products/' . $image->hashName(), $product->photos->first()->photo);
    }

    public function test_should_post_product_photos_endpoint_should_save_multiple_photos_by_upload()
    {
        $token = $this->makeUserToken();
        $product = Product::factory()->create();

        $image = UploadedFile::fake()->image('produto-foto-1.jpg');
        $image2 = UploadedFile::fake()->image('produto-foto-2.jpg');
        $image3 = UploadedFile::fake()->image('produto-foto-3.jpg');

        $response = $this->post('/api/products/1/photos',
            [
                'photos' => [
                    $image,
                    $image2,
                    $image3
                ]
            ],
            [
                'Content-Type' => 'application/form-data',
                'Authorization' => 'Bearer ' . $token
            ]
        );

        Storage::disk('public')->assertExists('products/' . $image->hashName());
        Storage::disk('public')->assertExists('products/' . $image2->hashName());
        Storage::disk('public')->assertExists('products/' . $image3->hashName());

        $photos = $product->photos;

        $this->assertEquals('products/' . $image->hashName(), $photos[0]->photo);
        $this->assertEquals('products/' . $image2->hashName(), $photos[1]->photo);
        $this->assertEquals('products/' . $image3->hashName(), $photos[2]->photo);
    }

    public function test_should_validate_uploaded_product_photos_as_image_mime_type()
    {
        $token = $this->makeUserToken();
        $product = Product::factory()->create();
        $pdf = UploadedFile::fake()->create('book.pdf', 1024, 'application/pdf');

        $response = $this->post('/api/products/1/photos',
            [
                'photos' => [
                    $pdf
                ]
            ],
            [
                'Content-Type' => 'application/form-data',
                'Accept'      => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertUnprocessable();
        $response->assertJson(fn(AssertableJson $json) =>
            $json->hasAll(['message', 'errors'])
        );

        $response->assertJsonValidationErrorFor('photos.0');

        $this->assertEquals('Arquivo de imagem inválido!', $response->json('errors')['photos.0'][0]);
    }

    public function test_should_product_photos_post_endpoint_throw_an_unauthorized_status()
    {
        Product::factory()->create();

        $response = $this->postJson('/api/products/1/photos', []);
        $response->assertUnauthorized();
    }

    public function test_should_product_photos_get_endpoint_returns_product_photos()
    {
        $product = Product::factory()->create();
        ProductPhoto::factory(3)->sequence(
            ['photo' => 'image1.jpg', 'product_id' => $product->id],
            ['photo' => 'image2.jpg', 'product_id' => $product->id],
            ['photo' => 'image3.jpg', 'product_id' => $product->id]
        )->create();

        $token = $this->makeUserToken();

        $response = $this->getJson('/api/products/1/photos', ['Authorization' => 'Bearer ' . $token]);
        $response->assertJson(fn(AssertableJson $json) =>
        $json
            ->whereAll([
                'data.0.photo' => 'image1.jpg',
                'data.1.photo' => 'image2.jpg',
                'data.2.photo' => 'image3.jpg',
            ])
            ->count('data', 3)->etc());
    }

    public function test_should_product_photos_delete_endpoint_remove_a_photo()
    {
        $token = $this->makeUserToken();

        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('image.jpg');

        $this->postJson('api/products/1/photos', ['photos' => [$image]], [
            'Content-Type' => 'application/form-data',
            'Authorization' => 'Bearer ' . $token
        ]);

        $response = $this->deleteJson('api/products/1/photos/1', [], ['Authorization' => 'Bearer ' . $token]);
        $response->assertNoContent();

        Storage::disk('public')->assertMissing('products/' . $image->hashName());
        $this->assertCount(0, $product->photos);
    }
}
