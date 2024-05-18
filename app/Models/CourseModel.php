<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseModel extends Model
{
    use HasFactory;
    
    
    protected $table ="courses";
    protected $fillable=[
        "name_course",
        "teacher_id",
        "description",
        
    ];
}
