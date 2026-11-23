<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = ['vendor_id','category_id','name','unit','description','price','stock','image','is_active'];
    protected function casts(): array { return ['price' => 'integer','stock' => 'integer','is_active' => 'boolean']; }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('assets/images/product-placeholder.svg');
        }

        if (str_starts_with($this->image, 'products/')) {
            return Storage::disk('public')->exists($this->image)
                ? Storage::disk('public')->url($this->image)
                : asset('assets/images/product-placeholder.svg');
        }

        return asset('assets/images/'.$this->image);
    }
}
