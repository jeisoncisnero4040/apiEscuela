<?php

namespace App\Mappers;
use App\Models\Usermodel;

class CommetsMapper{
    protected $commentsUnmapping;

    public function __construct($commentsUnmapping)
    {
        $this->commentsUnmapping=$commentsUnmapping;

    }
    public function mapperCommetsForAtivity(){
        
        $commentsMappend=[];
        foreach($this->commentsUnmapping as $commentUnmapped){
            $userId=$commentUnmapped['user_id'];
            $commenId=$commentUnmapped['id'];
            $comment=$commentUnmapped['comment'];
            $finishDate=$commentUnmapped['updated_at'];

            $user=Usermodel::find($userId);
            
            $userName=$user->name;
            $roles = [
                1 => "admin",
                2 => "teacher",
                3 => "student"
            ];
            $rolName = $roles[$user->rol_id] ?? 'Unknown';

            $commentMapped=[
                'id'=>$commenId,
                'comment'=>$comment,
                'user_name'=>$userName,
                'rol'=>$rolName,
                'date'=>$finishDate

            ];
            array_push($commentsMappend,$commentMapped);
            
        }
        return $commentsMappend;
    }
}