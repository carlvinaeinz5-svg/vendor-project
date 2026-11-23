<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_products_reference_matching_local_image_assets(): void
    {
        $this->seed(MarketplaceSeeder::class);

        $expectedImages = [
            'Fresh Tomatoes' => 'tomatoes.jpg',
            'Fresh Spinach' => 'spinach.jpg',
            'Plastic Bucket' => 'bucket.jpg',
            'Cooking Pot' => 'cooking pot.jpg',
            'Cotton Shirt' => 'shirts.jpg',
            'Kitenge Fabric' => 'shirts.jpg',
        ];

        foreach ($expectedImages as $name => $image) {
            $this->assertSame($image, Product::where('name', $name)->value('image'));
            $this->assertFileExists(public_path('assets/images/'.$image));
        }
    }
}
