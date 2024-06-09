<?php
namespace App\Helpers;

use App\Models\ActivityModel;
use App\Models\ScheduleModel;
use App\Models\CourseModel;
use Carbon\Carbon;

class ScheduleManager {
    protected $dataActivity;

    public function __construct($dataActivity) {
        $this->dataActivity = $dataActivity;
    }

    public function teacherIsInActivity() {
        $activity = ActivityModel::find($this->dataActivity['activity_id']);
        if (!$activity) {
            return false;
        }
        
        $course = CourseModel::find($activity->course_id);
        if (!$course) {
            return false;
        }

        return $course->teacher_id == $this->dataActivity['teacher_id'];
    }

    public function verificateThatActivityIsFree()
    {
        $activity_id = $this->dataActivity['activity_id'];
        $start_hour = Carbon::createFromFormat('d-m-Y H:i', $this->dataActivity['start_hour']);
        $end_hour = Carbon::createFromFormat('d-m-Y H:i', $this->dataActivity['end_hour']);

        return !ScheduleModel::where('activity_id', $activity_id)
            ->where(function($query) use ($start_hour, $end_hour) {
                $query->whereBetween('start_hour', [$start_hour, $end_hour])
                    ->orWhereBetween('end_hour', [$start_hour, $end_hour])
                    ->orWhere(function($query) use ($start_hour, $end_hour) {
                        $query->where('start_hour', '<=', $start_hour)
                                ->where('end_hour', '>=', $end_hour);
                    });
            })
            ->exists();
    }

    public function verificateIfTeacherIsFree()
    {
        $teacher_id = $this->dataActivity['teacher_id'];
        $start_hour = Carbon::createFromFormat('d-m-Y H:i', $this->dataActivity['start_hour']);
        $end_hour = Carbon::createFromFormat('d-m-Y H:i', $this->dataActivity['end_hour']);

        return !ScheduleModel::where('teacher_id', $teacher_id)
            ->where(function($query) use ($start_hour, $end_hour) {
                $query->whereBetween('start_hour', [$start_hour, $end_hour])
                      ->orWhereBetween('end_hour', [$start_hour, $end_hour])
                      ->orWhere(function($query) use ($start_hour, $end_hour) {
                          $query->where('start_hour', '<=', $start_hour)
                                ->where('end_hour', '>=', $end_hour);
                      });
            })
            ->exists();
    }
    public function verificateIfDateIsValid(){
        $start_hour = Carbon::createFromFormat('d-m-Y H:i', $this->dataActivity['start_hour']);
        $end_hour = Carbon::createFromFormat('d-m-Y H:i', $this->dataActivity['end_hour']);

         
        if($start_hour >= $end_hour){
            return false;
        }
        if(now()>=$start_hour){
            return false;
        }
        return true;
        

    }
}
