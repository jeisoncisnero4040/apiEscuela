<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFileModel extends Model
{
    use HasFactory;
    protected $table='files_activity';
    protected $fillable=[
        'activity_id',
        'file_url',
        'description'
    ];
}
