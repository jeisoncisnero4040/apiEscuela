<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitiesComentsModel extends Model
{
    use HasFactory;
    protected $table='activity_coments';
    protected $fillable=[
        'activity_id',
        'user_id',
        'comment'
    ];
}
