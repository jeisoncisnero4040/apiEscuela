<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdviceModel extends Model
{
    use HasFactory;
    protected $table='advices';
    protected $fillable=[
        'text',
        'image_url'
    ];
}
