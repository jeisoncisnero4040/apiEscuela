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

    /**
     * @OA\Get(
     *     path="/api/courses",
     *     tags={"courses"},
     *     summary="Retrieve all courses",
     *     description="Returns a list of all courses",
     *     @OA\Response(
     *         response=200,
     *         description="A list with courses",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Courses retrieved successfully"
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
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name_course", type="string", example="big data"),
     *                 @OA\Property(property="teacher_id", type="integer",   example="2"),
     *                 @OA\Property(property="description", type="string", example="curso big data"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No courses found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Courses not found"
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
     *                      @OA\Property(property="data", type="object", example={})
     *                   )
     *             )
     *         )
     *     )
     * )
     */
    
    public function getAllCourses(){
        $courses=CourseModel::all();
        if($courses->isEmpty()){
            $response=[
                'message'=>'Courses not found',
                'status'=>404,
                'data'=>[],
            ];
            return response()->json($response,200);
        }
        $response=[
            'message'=>'success',
            'status'=>200,
            'data'=>$courses,
        ];
        return response()->json($response,200);


    }

    /**
     * @OA\Get(
     *     path="/api/courses/{id}",
     *     tags={"courses"},
     *     summary="Retrieve a course by ID",
     *     description="Returns a single course by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the course to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="success"
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
         *                 @OA\Property(property="id", type="integer", example=1),
         *                 @OA\Property(property="name_course", type="string", example="big data"),
         *                 @OA\Property(property="teacher_id", type="integer",   example="2"),
         *                 @OA\Property(property="description", type="string", example="curso big data"),
         *                 @OA\Property(property="created_at", type="string", format="date-time"),
         *                 @OA\Property(property="updated_at", type="string", format="date-time")
         *             )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
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
     *                      @OA\Property(property="data", type="object", example={})
     *                   )
     *             )
     *         )
     *     )
     * )
     */

    public function getCourseById($id){
        $course=CourseModel::find($id);
        if(!$course){
            $response=[
                'message'=>'Course not found',
                'status'=>404,
                'data'=>[],
            ];
            return response()->json($response,200);
        }
        $response=[
            'message'=>'success',
            'status'=>200,
            'data'=>$course,
        ];
        return response()->json($response,200);

    }
    /**
     * @OA\Get(
     *     path="/api/courses/teacher/{id}",
     *     tags={"courses"},
     *     summary="Retrieve courses by teacher ID",
     *     description="Returns a list of courses taught by a specific teacher",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the teacher",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Courses retrieved successfully",
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
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name_course", type="string", example="big data"),
     *                     @OA\Property(property="teacher_id", type="integer", example=2),
     *                     @OA\Property(property="description", type="string", example="curso big data"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Teacher not found or no courses found for the teacher",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Teacher not found"
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

    public function getCoursesByTeacherId($id){
        $teacher=Usermodel::find($id);
        if(!$teacher || $teacher->id_rol !=2){
            $response=[
                'message'=>'teacher not found',
                'status'=>404,
                'data'=>[],
            ];
            return response()->json($response,404);
        }
        $coursesByTeacher = CourseModel::where('teacher_id', $teacher->id)->get();
        if($coursesByTeacher->isEmpty()){
            $response=[
                'message'=>'Courses by teacher not found',
                'status'=>404,
                'data'=>[],
            ];
            return response()->json($response,404);

        }
        $response=[
            'message'=>'succes',
            'status'=>200,
            'data'=>$coursesByTeacher,
        ];
        return response()->json($response,200);

    }
    /**
     * @OA\Get(
     *     path="/api/courses/not_teacher",
     *     tags={"courses"},
     *     summary="Retrieve all courses",
     *     description="Returns a list of all courses whitout teacher",
     *     @OA\Response(
     *         response=200,
     *         description="A list with courses whitout teacher",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="success"
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
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name_course", type="string", example="big data"),
     *                 @OA\Property(property="teacher_id", type="integer",   example="2"),
     *                 @OA\Property(property="description", type="string", example="curso big data"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No courses found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No courses without teachers found"
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
     *                      @OA\Property(property="data", type="object", example={})
     *                   )
     *             )
     *         )
     *     )
     * )
     */
    public function getCoursesWithNullTeacher(){
        $CoursesWithNullTeacher=CourseModel::where('teacher_id',null)->get();
        if($CoursesWithNullTeacher ->isEmpty()){
            $response=[
                'message'=>'No courses without teachers found',
                'status'=>404,
                'data'=>[],
            ];
            return response() ->json($response,404);
        

        }
        $response=[
            'message'=>'success',
            'status'=>200,
            'data'=>$CoursesWithNullTeacher,
        ];
        return response() ->json($response,200);

    }
    /**
     * @OA\Delete(
     *     path="/api/courses",
     *     tags={"courses"},
     *     summary="Delete course by id",
     *     description="Delete a course by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the course to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="success"
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
     *         description="Course not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="course not found"
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
     *                 example="internal server error"
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

    public function deleteCourse($id){
        $course=CourseModel::find($id);
        if(!$course){
            $response=[
                'message'=>'Course not found',
                'status'=>404,
                'data'=>[],
            ];
            return response() ->json($response,404);

        }
        try{
            $course->delete();
            $response=[
                'message'=>'Success',
                'status'=>200,
                    
            ];
            return response() ->json($response,404);
    
            
        }catch (\Exception $e) {
            $data = [
                "message" => 'error',
                'status' => 500,
                'errors' => $e->getMessage()
            ];
            return response()->json($data, 500);
        }
        
        
        

    }
    /**
     * @OA\Patch(
     *     path="/api/courses/{id}",
     *     summary="Update course by ID",
     *     tags={"courses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course to update",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Course data to update",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name_course", type="string", example="Big Data"),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="New course on PHP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name_course", type="string", example="Big Data"),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="New course on PHP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="errors", type="string", example="Course not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="teacher not valid"),
     *             @OA\Property(property="error", type="string", example="bad request"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating course",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */


    public function updateCourseById(Request $request,$id){

        $course = CourseModel::find($id);
        
        if (!$course) {
            $data = [
                "message" => 'error',
                'status' => 404,
                'errors' => 'course not found'
            ];
            return response()->json($data, 404);    
        }
        
        $bodyData=$request->all();
        if(isset($bodyData['teacher_id']) && $bodyData['teacher_id'] != 2){
            $data = [
                "message" => 'teacher no valid',
                'error'=>'bad request',
                'status' => 400,
                'data' => []
            ];
            
            return response()->json($data, 400);
        }
 
        try {
        
            $course->update($bodyData);
            
            $data = [
                "message" => 'success',
                'status' => 200,
                'data' => CourseModel::find($id)
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
    
    

