<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseModel extends Model
{
    use HasFactory;
    
    
    protected $table ="courses";
    protected $fillable=[
        "name",
        "teacher_id",
        "description",
        "image_url",
        "end_date",
        "start_date",
        
    ];
}
