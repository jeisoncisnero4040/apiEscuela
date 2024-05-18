<?php
namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Models\Usermodel;
use App\Models\CourseModel;

 
class CourseController extends Controller{

    /**
     * @OA\Post(
     *     path="/api/courses",
     *     summary="Create a new course",
     *     tags={"courses"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Course details",
     *         @OA\JsonContent(
     *             required={"name_course", "teacher_id", "description"},
     *             @OA\Property(property="name_course", type="string", example="Course Name"),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Course Description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=201),
     *              
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="errors", type="object", example={"name_course":{"The name_course field is required"}}),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Teacher not found or not a teacher",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="teacher not found or not a teacher"),
     *             @OA\Property(property="status", type="integer", example=404),
     *              
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating course",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating course"),
     *             @OA\Property(property="errors", type="string"),
     *             @OA\Property(property="status", type="integer", example=500),
     *              
     *         )
     *     )
     * )
     */


    public function createCourse(Request $request){
        $validator = Validator::make($request->all(), [
            'name_course' => 'required',
            'teacher_id' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            $response = [
                'message' => 'error',
                'error' => $validator->errors(),
                'status' => 400,
                'data' => [],
            ];
            return response()->json($response, 400);
        }

        $user = Usermodel::find($request->input('teacher_id'));
        if (!$user || $user->id_rol!= 2) {
            $response = [
                'message' => 'error',
                'error' => 'teacher not found or not a teacher',
                'status' => 404,
                'data' => $user->all(),
            ];
            return response()->json($response, 404);
        }

        try {
            $course = CourseModel::create([
                'name_course' => $request->input('name_course'),
                'teacher_id' => $request->input('teacher_id'),
                'description' => $request->input('description'),
            ]);

            $response = [
                'message' => 'success',
                'status' => 201,
                'data' => $course,
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating course',
                'errors' => $e->getMessage(),
                'status' => 500,
                'data' => []
            ], 500);
        }
    }

    
}
