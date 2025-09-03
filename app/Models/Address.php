<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Address",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="street", type="string"),
 *     @OA\Property(property="city", type="string"),
 *     @OA\Property(property="state", type="string"),
 *     @OA\Property(property="zip", type="string"),
 *     @OA\Property(property="country", type="string"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Address extends Model
{
    use HasFactory;



    protected $fillable = [
        'user_id',
        'street',
        'city',
        'state',
        'zip',
        'country',
        'type', // delivery, billing
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
