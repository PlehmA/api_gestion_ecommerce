<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="ProductModel",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="stock", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer'
    ];

    protected $dates = ['deleted_at'];

    // Scopes para filtros personalizados
    public function scopeMinPrice($query, $minPrice)
    {
        return $query->where('price', '>=', $minPrice);
    }

    public function scopeMaxPrice($query, $maxPrice)
    {
        return $query->where('price', '<=', $maxPrice);
    }

    public function scopeInStock($query, $inStock = true)
    {
        if ($inStock === 'true' || $inStock === true) {
            return $query->where('stock', '>', 0);
        }
        return $query;
    }
}
