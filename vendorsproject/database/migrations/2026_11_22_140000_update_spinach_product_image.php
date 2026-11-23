<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('name', 'Fresh Spinach')
            ->where('image', 'spinach.svg')
            ->update(['image' => 'spinach.jpg']);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('name', 'Fresh Spinach')
            ->where('image', 'spinach.jpg')
            ->update(['image' => 'spinach.svg']);
    }
};
