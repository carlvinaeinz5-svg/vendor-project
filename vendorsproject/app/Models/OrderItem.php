<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id','vendor_id','product_id','quantity','unit_price','subtotal','status'];

    public function statusLabelFor(string $fulfillmentMethod): string
    {
        return match ($this->status) {
            'confirmed' => 'Confirmed',
            'ready' => 'Ready for '.($fulfillmentMethod === 'delivery' ? 'delivery' : 'collection'),
            'completed' => $fulfillmentMethod === 'delivery' ? 'Delivered' : 'Collected',
            'cancelled' => 'Cancelled',
            'unavailable' => 'Unavailable',
            default => 'Pending',
        };
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
