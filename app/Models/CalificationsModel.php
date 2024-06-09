<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalificationsModel extends Model
{
    use HasFactory;
    protected $table ='califications';
    protected $fillable=[
        'activity_id',
        'student_id',
        'calification'
    ];
}
