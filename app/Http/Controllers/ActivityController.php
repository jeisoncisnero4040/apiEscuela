<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\UserModel;
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
     *             required={"name", "course_id", "text", "calification"},
     *             @OA\Property(property="name", type="string", example="Ver video"),
     *             @OA\Property(property="course_id", type="integer", example=1),
     *             @OA\Property(property="video_url", type="string", example="www.youtube.com&12343124"),
     *             @OA\Property(property="text", type="string", example="Ver video"),
     *             @OA\Property(property="calification", type="float", example=10.0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Actividad creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Ver video"),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="video_url", type="string", example="www.youtube.com&12343124"),
     *                 @OA\Property(property="text", type="string", example="Ver video"),
     *                 @OA\Property(property="calification", type="float", example=10.0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="errors", type="object", example={"name_course":{"El campo name_course es requerido"}}),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profesor no encontrado o no es un profesor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="Profesor no encontrado o no es un profesor"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creando la actividad",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creando la actividad"),
     *             @OA\Property(property="errors", type="string"),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function createActivity(Request $request){

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string',
            'video_url' => 'nullable|string|url',
            'text' => 'required|string',
            'calification' => 'required|numeric|min:0|max:10',
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
                'text' => $request->text,
                'calification' => $request->calification,
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
     *                     @OA\Property(property="video_url", type="string", example="www.youtube.com&12343124"),
     *                     @OA\Property(property="text", type="string", example="Ver video"),
     *                     @OA\Property(property="calification", type="float", example=10.0)
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
     *                 
     *               type="object",
     *               @OA\Property(property="name", type="string", example="Ver video"),
     *               @OA\Property(property="course_id", type="integer", example=1),
     *               @OA\Property(property="video_url", type="string", example="www.youtube.com&12343124"),
     *               @OA\Property(property="text", type="string", example="Ver video"),
     *               @OA\Property(property="calification", type="float", example=10.0)
     *             )
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

        $activities=ActivityModel::all();
        if($activities->isEmpty()){
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
            'data'=>$activities
        ];
        return response()->json($response,404);



    }
    public function getActivitiesByCourseId($id){

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
        return response()->json($response,404);


    }

    
}
