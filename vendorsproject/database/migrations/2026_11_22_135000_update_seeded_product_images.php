<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->where('name', 'Fresh Tomatoes')->where('image', 'tomatoes.svg')->update(['image' => 'tomatoes.jpg']);
        DB::table('products')->where('name', 'Plastic Bucket')->where('image', 'bucket.svg')->update(['image' => 'bucket.jpg']);
        DB::table('products')->where('name', 'Cooking Pot')->where('image', 'bucket.svg')->update(['image' => 'cooking pot.jpg']);
        DB::table('products')->whereIn('name', ['Cotton Shirt', 'Kitenge Fabric'])->where('image', 'shirt.svg')->update(['image' => 'shirts.jpg']);
    }

    public function down(): void
    {
        DB::table('products')->where('name', 'Fresh Tomatoes')->where('image', 'tomatoes.jpg')->update(['image' => 'tomatoes.svg']);
        DB::table('products')->where('name', 'Plastic Bucket')->where('image', 'bucket.jpg')->update(['image' => 'bucket.svg']);
        DB::table('products')->where('name', 'Cooking Pot')->where('image', 'cooking pot.jpg')->update(['image' => 'bucket.svg']);
        DB::table('products')->whereIn('name', ['Cotton Shirt', 'Kitenge Fabric'])->where('image', 'shirts.jpg')->update(['image' => 'shirt.svg']);
    }
};
