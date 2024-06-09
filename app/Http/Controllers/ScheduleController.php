<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ScheduleModel;
use App\Helpers\ScheduleManager;
use App\Mappers\ScheduleMapper;
use App\Models\ActivityModel;
use App\Models\StudentModel;
use Carbon\Carbon;


class ScheduleController extends Controller{
    /**
     * @OA\Post(
     *     path="/api/schedules",
     *     tags={"schedules"},
     *     summary="Create a new schedule for an activity",
     *     description="Create a new schedule for a given activity, validating date and time, teacher availability, and activity schedule conflicts.",
     *     operationId="createScheduleForActivity",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Schedule data",
     *         @OA\JsonContent(
     *             required={"activity_id", "teacher_id", "start_hour", "end_hour"},
     *             @OA\Property(property="activity_id", type="integer", example=1),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="start_hour", type="string", format="date-time", example="2024-06-10 08:00:00"),
     *             @OA\Property(property="end_hour", type="string", format="date-time", example="2024-06-10 09:00:00"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Schedule created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="activity_id", type="integer", example=1),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="start_hour", type="string", format="date-time", example="2024-06-10 08:00:00"),
     *             @OA\Property(property="end_hour", type="string", format="date-time", example="2024-06-10 09:00:00"),
     *          )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="bad request"),
     *             @OA\Property(property="error", type="string", example="date invalid"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={}),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error creating schedule"),
     *             @OA\Property(property="errors", type="string", example="Internal server error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={}),
     *         )
     *     )
     * )
     */

    public function CreateScheduleForActivity(Request $request){
         
        $validator = Validator::make($request->all(), [
            'activity_id' => 'required|numeric|exists:activities,id',
            'teacher_id' => 'required|numeric|exists:users,id',
            'start_hour' => 'required|date_format:d-m-Y H:i',
            'end_hour' => 'required|date_format:d-m-Y H:i',
        ]);

         
        if ($validator->fails()){
            $response = [
                'message' => 'bad request',
                'error' => $validator->errors(),
                'status' => 400,
                'data' => $request->all()
            ];
            return response()->json($response, 400);
        }

         
        $scheduleManager = new ScheduleManager($request->all());

        if(!$scheduleManager->verificateIfDateIsValid()){
            $response = [
                'message' => 'bad request',
                'error' => 'date invalid',
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }

        if (!$scheduleManager->teacherIsInActivity()){
            $response = [
                'message' => 'bad request',
                'error' => 'teacher invalid',
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }
        

        if (!$scheduleManager->verificateThatActivityIsFree()){
            $response = [
                'message' => 'bad request',
                'error' => 'activity already in this schedule',
                'status' => 400,
                'data' => $request->all()
            ];
            return response()->json($response, 400);
        }

        if (!$scheduleManager->verificateIfTeacherIsFree()){
            $response = [
                'message' => 'bad request',
                'error' => 'teacher already in this schedule',
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }

        try {
             
            $schedule = ScheduleModel::create([
                'activity_id' => $request->input('activity_id'),
                'start_hour' => Carbon::createFromFormat('d-m-Y H:i', $request->input('start_hour')),
                'end_hour' => Carbon::createFromFormat('d-m-Y H:i', $request->input('end_hour')),
                'teacher_id' => $request->input('teacher_id'),
            ]);

            $response = [
                'message' => 'success',
                'status' => 201,
                'data' => $schedule,
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating schedule',
                'errors' => $e->getMessage(),
                'status' => 500,
                'data' => []
            ], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/schedule/teacher/{id}",
     *     tags={"schedules"},
     *     summary="Get schedule for a specific teacher by ID",
     *     description="Retrieve the schedule for a teacher specified by their ID.",
     *     operationId="getScheduleForTeacherId",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the teacher",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="course", type="string", example="programacion 1"),
     *                     @OA\Property(property="activity", type="string", example="ver video"),
     *                     @OA\Property(property="start_hour", type="string", format="date-time", example="2024-06-10 08:00:00"),
     *                     @OA\Property(property="end_hour", type="string", format="date-time", example="2024-06-10 09:00:00")
     *                    
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Teacher not found or has no schedule",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="teacher has not schedule"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */

    public function getScheduleForTeacherId($id){
        $scheduleOfTeacher=ScheduleModel::where('teacher_id',$id)->get();
        if($scheduleOfTeacher->isEmpty()){
            $response=[
                'messagge'=>'failed',
                'error'=>'teacher has not schedule',
                'status'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);

        }
        $schedulemapper=new ScheduleMapper($scheduleOfTeacher);
        $dataSchhedule=$schedulemapper->mapperScheduleForTeacher();

        $response=[
            'message'=>'success',
            'status'=>200,
            'data'=>$dataSchhedule
        ];
        return response()->json($response,200);

    }
     /**
     * @OA\Get(
     *     path="/api/schedule/student/{id}",
     *     tags={"schedules"},
     *     summary="Get schedule for a specific student by ID",
     *     description="Retrieve the schedule for a student specified by their ID.",
     *     operationId="getScheduleByStudentId",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the student",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="course", type="string", example="programacion 1"),
     *                     @OA\Property(property="acticity", type="string", example="ver video"),
     *                     @OA\Property(property="name teacher", type="string", example="Alberto Higuera"),
     *                     @OA\Property(property="start_hour", type="string", format="date-time", example="2024-06-10 08:00:00"),
     *                     @OA\Property(property="end_hour", type="string", format="date-time", example="2024-06-10 09:00:00"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User is not a student or has no schedule",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="user is not student"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */

    public function getScheduleByStudentId($id) {
         
        $coursesId = StudentModel::where('user_id', $id)->pluck('course_id');

        if($coursesId->isEmpty()){
            $response=[
            'message'=>'failed',
            'error'=>'user is not student',
            'status'=>404,
            'data'=>[]
            ];
            return response()->json($response,404);
        }
    
         
        $allActivitiesFromStudent = [];
    
         
        foreach ($coursesId as $courseId) {
            $activitiesIdByCourseId = ActivityModel::where('course_id', $courseId)->pluck('id')->toArray();
            $allActivitiesFromStudent = array_merge($allActivitiesFromStudent, $activitiesIdByCourseId);
        }
        
        $schedule=[];
        foreach($allActivitiesFromStudent as $activityId){
            $scheduleActivity=ScheduleModel::where('activity_id',$activityId)->get();
            
            $scheduleMapper=new ScheduleMapper($scheduleActivity);
            $scheduleMappedForActivity=$scheduleMapper->mapperScheduleForStudent();
            $schedule=array_merge($schedule,$scheduleMappedForActivity);

        }
        $response=[
            'message'=>'succes',
            'status'=>200,
            'data'=>$schedule
            ];
            
        return response()->json($response, 200);
    }
    /**
     * @OA\Delete(
     *     path="/api/schedule/{id}",
     *     tags={"schedules"},
     *     summary="Delete schedule by ID",
     *     description="Delete a schedule specified by its ID.",
     *     operationId="deleteScheduleById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the schedule",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Schedule deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="schedule was delete"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Schedule not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="schedule not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="Internal server error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */

    public function deleteScheduleById($id){
        $schedule=ScheduleModel::find($id);
        if(!$schedule){
            $response=[
            'message'=>'failed',
            'error'=>'schedule not found',
            'status'=>404,
            'data'=>[]
            ];
            return response()->json($response,404);
        }
        try{
            $schedule->delete();
            $response=[
                'message'=>'schedule was delete',
                'status'=>200

            ];
            return  response()-> json($response,200);


        }
        catch(\Exception $e){
            $response=[
                'message'=>'failed',
                'error'=>$e->getMessage(),
                'status'=>500,
                'data'=>[]
                ];
                return response()->json($response,500);
        }

    }

    /**
     * @OA\Patch(
     *     path="/api/schedule/{id}",
     *     tags={"schedules"},
     *     summary="Update schedule by ID",
     *     description="Update a schedule specified by its ID.",
     *     operationId="updateScheduleById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the schedule",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Schedule data",
     *         @OA\JsonContent(
     *             required={"start_hour", "end_hour"},
     *             @OA\Property(property="start_hour", type="string", format="date-time", example="2024-06-10 08:00:00"),
     *             @OA\Property(property="end_hour", type="string", format="date-time", example="2024-06-10 09:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Schedule updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="start_hour", type="string", format="date-time", example="2024-06-10 08:00:00"),
     *                 @OA\Property(property="end_hour", type="string", format="date-time", example="2024-06-10 09:00:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="bad request"),
     *             @OA\Property(property="error", type="string", example="date is not valid"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Schedule not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="bad request"),
     *             @OA\Property(property="error", type="string", example="schedule not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="Internal server error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */


    public function updateSheduleById(Request $request, $id){
        $validator=Validator::make($request->all(),[
            'start_hour' => 'nullable|date_format:d-m-Y H:i',
            'end_hour' => 'nullable|date_format:d-m-Y H:i',
        ]);
        if ($validator->fails()){
            $response = [
                'message' => 'bad request',
                'error' => $validator->errors(),
                'status' => 400,
                'data' => $request->all()
            ];
            return response()->json($response, 400);
        }
        $scheduleManager = new ScheduleManager($request->all());
        if(!$scheduleManager->verificateIfDateIsValid()){
            $response = [
                'message' => 'bad request',
                'error' => 'date is not valid',
                'status' => 400,
                'data' => $request->all()
            ];
            return response()->json($response, 400);

        }

        $schedule=ScheduleModel::find($id);
        if(!$schedule){
            $response = [
                'message' => 'bad request',
                'error' => 'schedule not found',
                'status' => 404,
                'data' => $request->all()
            ];
            return response()->json($response, 404);
        }
        try{
            $schedule->update($request->all());
            $response = [
                'message' => 'success',
                'status' => 200,
                'data' => $request->all()
            ];
            return response()->json($response, 200);

        }
        catch(\Exception $e){
            $response = [
                'message' => 'success',
                'error'=>$e->getMessage(),
                'status' => 500,
                'data' => []
            ];
            return response()->json($response, 500);
        }
    }

}
