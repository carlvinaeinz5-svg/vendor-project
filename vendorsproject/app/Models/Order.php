<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['number','customer_id','customer_name','phone','delivery_note','fulfillment_method','delivery_address','payment_method','payment_reference','total','status'];

    public function fulfillmentLabel(): string
    {
        return $this->fulfillment_method === 'delivery' ? 'Delivery' : 'Collection';
    }

    public function handoffLabel(): string
    {
        return $this->fulfillment_method === 'delivery' ? 'delivery' : 'collection';
    }

    public function customer(): BelongsTo { return $this->belongsTo(User::class,'customer_id'); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
