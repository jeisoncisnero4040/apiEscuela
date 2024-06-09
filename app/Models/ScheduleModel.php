<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleModel extends Model
{
    use HasFactory;
    protected $table='schedules';
    protected $fillable=[
        'activity_id',
        'day',
        'start_hour',
        'end_hour',
        'teacher_id'
    ];

}
