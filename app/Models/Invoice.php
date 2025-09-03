<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Invoice",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="order_id", type="integer"),
 *     @OA\Property(property="billing_data", type="string"),
 *     @OA\Property(property="amount", type="number", format="float"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'billing_data',
        'amount',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
