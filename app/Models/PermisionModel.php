<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisionModel extends Model
{
    use HasFactory;
    protected $table ="permision_school";
    protected $fillable=[
        "name",
        "permision_detail",
        "expired_date"

    ];
}
