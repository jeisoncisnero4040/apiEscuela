<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CourseModel;
use App\Models\ActivityModel;

class ActivityController extends Controller{

    /**
     * @OA\Post(
     *     path="/api/activities",
     *     summary="Crear una nueva actividad",
     *     tags={"activities"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Detalles de la actividad",
     *         @OA\JsonContent(
     *             required={"course_id", "name", "description", "type_id"},
     *             @OA\Property(property="course_id", type="integer", example=1, description="ID del curso"),
     *             @OA\Property(property="name", type="string", example="Ver video", description="Nombre de la actividad"),
     *             @OA\Property(property="video_url", type="string", example="https://www.youtube.com/watch?v=12343124", description="URL del video"),
     *             @OA\Property(property="description", type="string", example="Ver video", description="Descripción de la actividad"),
     *             @OA\Property(property="type_id", type="integer", example=1, description="ID del tipo de actividad")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="activity created successfull",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ver video"),
     *                 @OA\Property(property="video_url", type="string", example="https://www.youtube.com/watch?v=12343124"),
     *                 @OA\Property(property="description", type="string", example="Ver video"),
     *                 @OA\Property(property="type_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="error", type="object", example="bad request"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Curso no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="course not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creando la actividad",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="internal server error"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function createActivity(Request $request){

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string',
            'video_url' => 'required|string|url',
            'description' => 'required|string',
            'type_id' => 'required|exists:activity_types,id',
        ]);

       
        if ($validator->fails()) {
            $response = [
                'message' => 'error',
                'error' => $validator->errors(),
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }

        try {
           
            $actividad = ActivityModel::create([
                'course_id' => $request->course_id,
                'name' => $request->name,
                'video_url' => $request->video_url,
                'description' => $request->description,
                'type_id' => $request->type_id,
            ]);

            $response = [
                'message' => 'success',
                'status' => 201,
                'data' => $actividad
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            $response = [
                'message' => 'error',
                'error' => $e->getMessage(),
                'status' => 500,
                'data' => []
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/activities",
     *     tags={"activities"},
     *     summary="Recuperar todas las actividades",
     *     description="Devuelve una lista de todas las actividades",
     *     @OA\Response(
     *         response=200,
     *         description="Una lista con actividades",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Actividades recuperadas exitosamente"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=200
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Ver video"),
     *                     @OA\Property(property="course_id", type="integer", example=1),
     *                     @OA\Property(property="video_url", type="string", example="https://www.youtube.com/watch?v=12343124"),
     *                     @OA\Property(property="description", type="string", example="Ver video"),
     *                     @OA\Property(property="activity_type", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-01T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="activities not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="activities not found"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=404
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     example={}
     *                 )
     *             )
     *         )
     *     )
     * )
     */


    public function getAllActivities(){
        $activities=ActivityModel::all();
        if($activities->isEmpty()){
            $response=[
                'message'=>'error',
                'error'=>'data_not_found',
                'status'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);
            

        }
        $response=[
            'message'=>'succes',
            'status'=>200,
            'data'=>$activities
        ];
        return response()->json($response,404);
        
    }
    /**
     * @OA\Get(
     *     path="/api/activities/{id}",
     *     summary="Get activity by ID",
     *     tags={"activities"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the activity to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Ver video"),
     *                     @OA\Property(property="course_id", type="integer", example=1),
     *                     @OA\Property(property="video_url", type="string", example="https://www.youtube.com/watch?v=12343124"),
     *                     @OA\Property(property="description", type="string", example="Ver video"),
     *                     @OA\Property(property="activity_type", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-01T12:00:00Z")
     *                 )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="errors", type="string", example="User not found"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function getActivityById($id){

        $activity=ActivityModel::find($id);
        if(!$activity){
            $response=[
                'message'=>'error',
                'error'=>'activity not found',
                'status'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);
            

        }
        $response=[
            'message'=>'succes',
            'status'=>200,
            'data'=>$activity
        ];
        return response()->json($response,404);



    }
    /**
     * @OA\Get(
     *     path="/activities/course/{id}",
     *     tags={"activities"},
     *     summary="Retrieve activietes by course ID",
     *     description="Returns a list of activities taught by a specific course",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the course",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activities retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=200
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Ver video"),
     *                     @OA\Property(property="course_id", type="integer", example=1),
     *                     @OA\Property(property="video_url", type="string", example="https://www.youtube.com/watch?v=12343124"),
     *                     @OA\Property(property="description", type="string", example="Ver video"),
     *                     @OA\Property(property="activity_type", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-01T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="course not found or no courses found for the teacher",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Course not found"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=404
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="data", type="object", example={})
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getActivitiesByCourseId($id){

        $course=CourseModel::find($id);
        if(!$course){
            $response=[
                'message'=>'error',
                'error'=>'course not exist',
                'status'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);

        }

        $activities=ActivityModel::where('course_id',$id)->get();
        if($activities->isEmpty()){
            $response=[
                'message'=>'error',
                'error'=>'course has not activities',
                'status'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);
            

        }
        $response=[
            'message'=>'succes',
            'status'=>200,
            'data'=>$activities
        ];
        return response()->json($response,200);


    }
    /**
     * @OA\Delete(
     *     path="/activities/{id}",
     *     tags={"activities"},
     *     summary="Delete an activity by ID",
     *     description="Delete an activity by its ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the activity",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Activity deleted successfully"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=200
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Activity not found"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=404
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="data", type="object", example={})
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Internal server error"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=500
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="data", type="object", example={})
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function deleteActivityById($id){
        $activityToDelete=ActivityModel::find($id);
        if(!$activityToDelete){
            $response=[
                'message'=>'failed',
                'error'=>'activity not found',
                'status'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);
        }
        try{
            $activityToDelete->delete();
            $response=[
                'message'=>'actyvity deleted succesfull',
                'error'=>'activity not found',
                'status'=>404,
                 
            ];
            return response()->json($response,404);
        }
        catch(\Exception $e){
            $response=[
                'message'=>'internal server error',
                'error'=> $e->getMessage(),
                'status'=>500,
                'data'=>[]
            ];
            return response()->json($response,500);

        }

    }
    /**
     * @OA\Patch(
     *     path="/api/activities/{id}",
     *     summary="Update activity by ID",
     *     tags={"activities"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the activity to update",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Activity data to update",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="primera clase"),
     *             @OA\Property(property="video_url", type="string", example="www.youtube.com&12343124"),
     *             @OA\Property(property="text", type="string", example="Detalles de la actividad"),
     *             @OA\Property(property="calification", type="float", example=8.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="primera clase"),
     *                 @OA\Property(property="video_url", type="string", example="www.youtube.com&12343124"),
     *                 @OA\Property(property="text", type="string", example="Detalles de la actividad"),
     *                 @OA\Property(property="calification", type="float", example=8.5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="errors", type="string", example="Activity not found"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Course not found"),
     *             @OA\Property(property="error", type="string", example="bad request"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating activity",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="errors", type="string", example="Error message"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function updateActivityById($id, Request $request){
         
        $activityToUpdate = ActivityModel::find($id);
        if (!$activityToUpdate) {
            $data = [
                "message" => 'error',
                'status' => 404,
                'errors' => 'Activity not found'
            ];
            return response()->json($data, 404);
        }
     
        $bodyData = $request->all();
     
        if (isset($bodyData['id_curso']) && !CourseModel::find($bodyData['id_curso'])) {
             $data = [
                 "message" => 'error',
                 'status' => 400,
                 'errors' => 'Course not valid',
                 'data' => []
             ];
             return response()->json($data, 400);
         }
     
        if (isset($bodyData['video_url'])) {
            $videoUrlValidator = Validator::make($bodyData, [
                'video_url' => 'nullable|string|url'
             ]);
            if ($videoUrlValidator->fails()) {
                 $data = [
                     "message" => 'error',
                     'status' => 400,
                     'errors' => 'Invalid video URL',
                     'data' => []
                 ];
                 return response()->json($data, 400);
             }
        }
     
         try {
            $activityToUpdate->update($bodyData);
            $data = [
                "message" => 'success',
                'status' => 200,
                'data' => $activityToUpdate
            ];
            return response()->json($data, 200);
        } catch (\Exception $e) {
            $data = [
                "message" => 'error',
                'status' => 500,
                'errors' => $e->getMessage()
            ];
            return response()->json($data, 500);
         }
     }
     
    

    
}
