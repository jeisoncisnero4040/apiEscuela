<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleModel extends Model
{
    use HasFactory;
    protected $table='schedule';
    protected $fillable=[
        'activity_id',
        'day',
        'star_hour',
        'end_hour'
    ];

}
