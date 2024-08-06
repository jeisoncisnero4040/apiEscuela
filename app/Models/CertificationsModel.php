<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificationsModel extends Model
{
    use HasFactory;
    protected $table ='certifications';
    protected $fillable=[
        'student_id',
        'certification',
        'string_calification',
        'numeric_calification',
        'observations'
    ];
}
