<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Usermodel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $table = "users";
    protected $fillable = [
        "name",
        "email",
        "password",
        "rol_id",
        "image_url",
    ];

     
}