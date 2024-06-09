<?php 

namespace App\Mappers;

use App\Models\ActivityModel;
use App\Models\CourseModel;
use App\Models\Usermodel;

class ScheduleMapper{
    protected $scheduleUnmapping;

    public function __construct($scheduleUnmapping) {
        $this->scheduleUnmapping = $scheduleUnmapping;
    }

    public function mapperScheduleForTeacher(){
        $mappedSchedule = [];

        foreach ($this->scheduleUnmapping as $schedule) {
            $activity = ActivityModel::find($schedule['activity_id']);
            $course = null;

            if ($activity) {
                $course = CourseModel::find($activity->course_id);
            }

            if ($course) {
                $mappedSchedule[] = [
                    'course' => $course->name,
                    'activity' => $activity->name,
                    'start_hour' =>$schedule['start_hour'],
                    'end_hour' =>$schedule['end_hour'],
                   
                ];
            }
        }

        return $mappedSchedule;
    }
    public function mapperScheduleForStudent(){
        $mappedSchedule = [];

        foreach ($this->scheduleUnmapping as $schedule) {
            $activity = ActivityModel::find($schedule['activity_id']);
            $course = null;

            if ($activity) {
                $course = CourseModel::find($activity->course_id);
            }

            if ($course) {
                $teacherName=Usermodel::where('id',$course->teacher_id)->pluck('name');
                $mappedSchedule[] = [
                    'course' => $course->name,
                    'name teacher'=>$teacherName[0],
                    'activity' => $activity->name,
                    'start_hour' =>$schedule['start_hour'],
                    'end_hour' =>$schedule['end_hour'],
                   
                ];
            }
        }

        return $mappedSchedule;
    }
}
