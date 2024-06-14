<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{

    use HasFactory;
    protected $table='payments';
    protected $fillable=[
        'product_id',
        'user_id',
        'value',
        'currency',
        'observations',
    ];
}
