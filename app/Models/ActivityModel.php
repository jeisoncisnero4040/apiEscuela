<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityModel extends Model
{
    use HasFactory;
    protected $table ="activities";
    protected $fillable=[
        "course_id",
        "name",
        "video_url",
        "text",
        "calification",

        
    ];
}
