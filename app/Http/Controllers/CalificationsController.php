<?php

namespace App\Http\Controllers;

use App\Mappers\CalificationsMapper;
use App\Models\ActivityModel;
use App\Models\CalificationsModel;
use App\Models\StudentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CalificationsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/califications",
     *     tags={"califications"},
     *     summary="Add a new calification",
     *     description="Create a new calification for a student in a specific activity.",
     *     operationId="addCalification",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Calification data",
     *         @OA\JsonContent(
     *             required={"activity_id", "student_id", "calification"},
     *             @OA\Property(property="activity_id", type="integer", example=1),
     *             @OA\Property(property="student_id", type="integer", example=1),
     *             @OA\Property(property="calification", type="number", format="float", example=8.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Calification created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="created"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="activity_id", type="integer", example=1),
     *                 @OA\Property(property="student_id", type="integer", example=1),
     *                 @OA\Property(property="calification", type="number", format="float", example=8.5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="bad request"),
     *             @OA\Property(property="error", type="object"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="internal server error"),
     *             @OA\Property(property="error", type="string", example="Internal server error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function addCalification(Request $requests){
        
        $validator=Validator::make($requests->all(),[
            'activity_id' => 'required|exists:activities,id',
            'student_id' => 'required|exists:students,id',
            'calification' => 'required|numeric|between:0.0,10.0'
        ]);

        if ($validator->fails()){
            $response=[
                'message'=>'bad request',
                'error'=>$validator->errors(),
                'status'=>400,
                'data'=>[]
            ];
            return response()->json($response,400);
        }
        $activity = ActivityModel::find($requests->input('activity_id'));
        $student = StudentModel::find($requests->input('student_id'));

        if ($activity->course_id != $student->course_id) {
            $response = [
                'message' => 'failed',
                'error' => 'student not valid',
                'status' => 400,
                'data' => [$student,$activity]
            ];
            return response()->json($response, 400);
        }
        try{
            $calification=CalificationsModel::create([
                'activity_id'=>$requests->input('activity_id'),
                'student_id'=>$requests->input('student_id'),
                'calification'=>$requests->input('calification')
            ]);
            $response=[
                'mesagge'=>'created',
                'status'=>201,
                'data'=>$calification
            ];
            return response()->json($response,201);
        }
        catch(\Exception $e){
            $response=[
                'message'=>'internal server error',
                'error'=>$e->getMessage(),
                'status'=>500,
                'data'=>[]
            ];
            return response()->json($response,500);
        }
    }
    /**
     * @OA\Get(
     *     path="/califications/{student_id}/activity/{activity_id}",
     *     summary="Get all grades for an activity for a student",
     *     tags={"califications"},
     *     @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         required=true,
     *         description="ID of the student",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="path",
     *         required=true,
     *         description="ID of the activity",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="info activity",
     *                     type="object",
     *                     @OA\Property(property="course", type="string", example="big data"),
     *                     @OA\Property(property="activity", type="string", example="read text")
     *                 ),
     *                 @OA\Property(
     *                     property="califications",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="calification 1", type="number", format="float", example=8)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="student not valid"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity has no grades",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="activity has not calification"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */


    public function getAllCalificationsActivityFromStudent($studentId,$activityId){

        $activity = ActivityModel::find($activityId);
        $student = StudentModel::find($studentId);

        if (!$activity|| !$student || $activity->course_id != $student->course_id) {
            $response = [
                'message' => 'failed',
                'error' => 'student not valid',
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }
        
        $califications=CalificationsModel::where('activity_id',$activityId)->
                                            where('student_id',$studentId)
                                            ->get();

        if($califications->isEmpty()){
            $response=[
                'message'=>'failed',
                'error'=>'activity has not calification',
                'status'=>404,
                'data'=>[]

            ];
            return response()->json($response,404);
        }
        $calificationMapper=new CalificationsMapper($califications);
        $calificationsMapped=$calificationMapper->mapperCalificatiosForStudent($activityId);

        $response=[
            'message'=>'success',
            'status'=>200,
            'data'=>$calificationsMapped
        ];
        return response()->json($response,200);


    }
    /**
     * @OA\Get(
     *     path="/califications/{student_id}/course/{course_id}",
     *     summary="Get all grades for a course for a student",
     *     tags={"califications"},
     *     @OA\Parameter(
     *         name="student_id",
     *         in="path",
     *         required=true,
     *         description="ID of the student",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         description="ID of the course",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="info activity",
     *                     type="object",
     *                     @OA\Property(property="course", type="string", example="big data"),
     *                     @OA\Property(property="teacher", type="string", example="John Doe")
     *                 ),
     *                 @OA\Property(
     *                     property="califications",
     *                     type="object",
     *                     @OA\Property(property="Activity 1", type="object",example={8.5,10,0.0}),
     *                     @OA\Property(property="Activity 2", type="array",
     *                         @OA\Items(
     *                             type="number",
     *                             format="float",
     *                             example=10
     *                         )
     *                     ),
     *                     @OA\Property(property="Activity 3", type="array",
     *                         @OA\Items(
     *                             type="number",
     *                             format="float",
     *                             example=5.4
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="student not valid"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No grades found for the student",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="student has not calificators"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function getAllCalificationsCourseFromStudent($studentId, $courseId) {
        $activities = ActivityModel::where('course_id', $courseId)->pluck('id');
    
        $calificationsUnmapped = [];
        foreach ($activities as $activityId) {
            $calificationsForActivity = CalificationsModel::where('activity_id', $activityId)
                ->where('student_id', $studentId)
                ->get();
                
            if (!$calificationsForActivity->isEmpty()) {
                $calificationsUnmapped = array_merge($calificationsUnmapped, $calificationsForActivity->toArray());
            }
        }

        if(!$calificationsUnmapped){
            $response=[
                'message'=>'failed',
                'error'=>'student has not calificators',
                'status'=>404,
                'data'=>[]
            ];
            return response() ->json($response,404);
        }
        $calificationMapper=new CalificationsMapper($calificationsUnmapped);
        $calificationsMapped=$calificationMapper->mapperCalificatiosForStudentCourse($courseId);
        
        $response=[
            'message'=>'succes',
            'status'=>200,
            'data'=>$calificationsMapped
        ];
        return response() ->json($response,200);
        
    }
    /**
     * @OA\Get(
     *     path="/califications/{id}",
     *     summary="Get a calification by ID",
     *     tags={"califications"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the calification",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="activity_id", type="integer", example=2),
     *                 @OA\Property(property="student_id", type="integer", example=3),
     *                 @OA\Property(property="calification", type="number", format="float", example=9.5),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-01T12:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-01T12:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Calification not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="calification not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function getCalificationById($id){
           
        $calification = CalificationsModel::find($id);
    
            
        if (!$calification) {
            $response = [
                'message' => 'failed',
                'error' => 'calification not found',
                'status' => 404,
                'data' => []
            ];
            return response()->json($response, 404);
        }
    
            
        $response = [
            'message' => 'success',
            'status' => 200,
            'data' => $calification
        ];
        return response()->json($response, 200);
    }
    /**
     * @OA\Delete(
     *     path="/califications/{id}",
     *     summary="Delete an calification by ID",
     *     tags={"califications"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the calification",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="calification deleted successfully"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="calification not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="calification not found"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function deleteCalificationById($id) {
        $calidicationToDelete = CalificationsModel::find($id);

        if (!$calidicationToDelete) {
            $response = [
                'message' => 'failed',
                'error' => 'calification not found',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $calidicationToDelete->delete();

        $response = [
            'message' => 'Calification deleted successfully',
            'status' => 200
        ];
        return response()->json($response, 200);
    }
 

    /**
     * @OA\Post(
     *     path="/califications/{id}",
     *     summary="Update the value of a grade by ID",
     *     tags={"califications"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the calification",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="New calification value",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="value", type="number", format="float", example=9.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Calification updated successfully"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="value", type="number", format="float", example=9.5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="Invalid input"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Calification not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="Calification not found"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function updateCalificationById(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'calification' => 'required|numeric|between:0.0,10.0'
        ]);

        if ($validator->fails()) {
            $response = [
                'message' => 'failed',
                'error' => $validator->errors(),
                'status' => 400
            ];
            return response()->json($response, 400);
        }

        $calification = CalificationsModel::find($id);

        if (!$calification) {
            $response = [
                'message' => 'failed',
                'error' => 'Calification not found',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $calification->calification = $request->input('calification');
        $calification->save();

        $response = [
            'message' => 'Calification updated successfully',
            'status' => 200,
            'data' => [
                'id' => $calification->id,
                'calification' => $calification->calification
            ]
        ];
        return response()->json($response, 200);
    }

            
    
    
}
