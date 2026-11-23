<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = [
        'Cash on delivery / collection' => 'Pay cash directly to the vendor when your order is handed over.',
        'Mobile Money (pay vendor directly)' => 'Pay the vendor by mobile money directly; confirm payment details with them.',
        'Bank transfer (pay vendor directly)' => 'Transfer to the vendor using payment details they provide.',
        'Card at handoff' => 'Pay the vendor by card at delivery or collection, if they accept it.',
    ];

    protected $fillable = ['order_id','vendor_id','method','reference','amount','status','confirmed_at'];
    protected function casts(): array { return ['confirmed_at' => 'datetime']; }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
}
