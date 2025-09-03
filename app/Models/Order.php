<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="address_id", type="integer"),
 *     @OA\Property(property="invoice_id", type="integer"),
 *     @OA\Property(property="total", type="number", format="float"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="products", type="array", @OA\Items(ref="#/components/schemas/Product")),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="address", ref="#/components/schemas/Address"),
 *     @OA\Property(property="invoice", ref="#/components/schemas/Invoice")
 * )
 */
class Order extends Model 
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'invoice_id',
        'total',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')->withPivot('quantity', 'price');
    }
}
