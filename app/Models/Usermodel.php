<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class Usermodel extends Model
{   use HasApiTokens,HasFactory;
    
    
    protected $table ="users";
    protected $fillable=[
        "name",
        "email",
        "password",
        "id_rol"
    ];
}
