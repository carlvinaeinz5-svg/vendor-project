<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = ['user_id','category_id','store_name','stall_location','phone','description','status','approved_at'];
    protected function casts(): array { return ['approved_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
}
