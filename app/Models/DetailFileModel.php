<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailFileModel extends Model
{
    use HasFactory;
    
    protected $table='detail_files';
    protected $fillable=[
        'activity_file_id',
        'student_id',
    ];
}
