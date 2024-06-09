<?php
namespace App\Mappers;

use App\Models\ActivityModel;
use App\Models\CourseModel;
use App\Models\Usermodel;

class CalificationsMapper{
    protected $calificationsUnmapping;

    public function __construct($calificationsUnmapping){
        $this->calificationsUnmapping=$calificationsUnmapping;

    }
    public function mapperCalificatiosForStudent($activityId){
        $activity=ActivityModel::find($activityId);
        $course=CourseModel::find($activity->course_id);
        
        $infoActivity=[
            'course'=>$course->name,
            'activity'=>$activity->name
        ];
        
        $calificationsMapped=[];
        $contador=1;
        foreach($this->calificationsUnmapping as $calificationUnmapping){

            $calification=[
                'id'=>$calificationUnmapping['id'],
                'calification '.$contador=>$calificationUnmapping['calification']
            ];

            array_push($calificationsMapped,$calification);
            $contador += 1;
        }
        $calificationsData=[
            'info activity'=>$infoActivity,
            'califications'=>$calificationsMapped
        ];
        
        return $calificationsData;
    }

    public function mapperCalificatiosForStudentCourse($courseId){
        $course=CourseModel::find($courseId);
        $user=Usermodel::find($course->teacher_id);
        
        $infoCourse=[
            'course'=>$course->name,
            'teacher'=>$user->name
        ];
        
        $calificationsMapped=[];
        foreach($this->calificationsUnmapping as $calificationUnmapping){

            $calificationOfActivity=$calificationUnmapping['calification'];
            $activityId=$calificationUnmapping['activity_id'];
            $activity=ActivityModel::find($activityId);
            $nameActivity=$activity->name;
            if(!isset($calificationsMapped[$nameActivity])){

                $calificationsMapped[$nameActivity]=[$calificationOfActivity];
            }
            else{
                array_push($calificationsMapped[$nameActivity],$calificationOfActivity);
            }
       

        }
        $calificationsData=[
            'info course'=>$infoCourse,
            'califications'=>$calificationsMapped
        ];
        
        return $calificationsData;
    }
    
}