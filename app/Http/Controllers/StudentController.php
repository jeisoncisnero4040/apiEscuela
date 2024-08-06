<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\StudentModel;
use App\Models\CourseModel;
use App\Models\Usermodel;

class StudentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/students",
     *     summary="Add a new student",
     *     tags={"students"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Student details",
     *         @OA\JsonContent(
     *             required={"user_id", "course_id"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="course_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Student added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="course_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="errors", type="object", example={"course_id": {"The course_id field is required"}}),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="student not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="internal server error"),
     *             @OA\Property(property="errors", type="string"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function createStudent(Request $request){
        $studentValidator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($studentValidator->fails()) {
            $response = [
                'message' => 'failed',
                'error' => $studentValidator->errors(),
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }

        if (Usermodel::find($request->input('user_id'))->rol_id != 3) {
            $response = [
                'message' => 'failed',
                'error' => 'student not found',
                'status' => 404,
                'data' => []
            ];
            return response()->json($response, 404);
        }

        $existingStudent = StudentModel::where('course_id', $request->input('course_id'))
                                       ->where('user_id', $request->input('user_id'))
                                       ->first();

        if ($existingStudent) {
            $response = [
                'message' => 'failed',
                'error' => 'Student already enrolled in this course',
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }

        try {
            $student = StudentModel::create($request->all());

            $response = [
                'message' => 'success',
                'status' => 201,
                'data' => $student
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            $response = [
                'message' => 'internal server error',
                'status' => 500,
                'errors' => $e->getMessage(),
                'data' => []
            ];
            return response()->json($response, 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/students",
     *     tags={"students"},
     *     summary="Retrieve all students that are in some course",
     *     description="Retrieve all students that are in some course",
     *     @OA\Response(
     *         response=200,
     *         description="A list with all students",
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
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1), 
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="hola@example.com")
     *                     ),
     *                     @OA\Property(
     *                         property="course",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1), 
     *                         @OA\Property(property="name", type="string", example="Big Data")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="students not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="students not found"
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

    public function getAllActiviesStudents(){
        $studentsActivies = StudentModel::all();
        if ($studentsActivies->isEmpty()) {
            $response = [
                'message' => 'failed',
                'errors' => 'students not found',
                'status' => 404,
                'data' => []
            ];
            return response()->json($response, 404);
        }

        $studentsInfoData = [];

        foreach ($studentsActivies as $student) {
            $infoUser = Usermodel::find($student['user_id']);
            $infoUser->makeHidden(['password', 'created_at', 'updated_at','rol_id']);
        
            $infoCourse = CourseModel::find($student['course_id']);
        
            $studentData = [
                'id' => $student['id'],
                'user' => $infoUser,
                'course' => [
                    'id' => $infoCourse->id,
                    'name_course' => $infoCourse->name,
                ],
            ];
        
            array_push($studentsInfoData, $studentData);
        }
        
         
        

        $response = [
            'message' => 'success',
            'status' => 200,
            'data' => $studentsInfoData
        ];
        return response()->json($response, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/students/{id}",
     *     tags={"students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the student to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     summary="Retrieve all students that are in some course",
     *     description="Retrieve all students that are in some course",
     * 
     *     @OA\Response(
     *         response=200,
     *         description="A list with all students",
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
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1), 
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="hola@example.com")
     *                     ),
     *                     @OA\Property(
     *                         property="course",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1), 
     *                         @OA\Property(property="name", type="string", example="Big Data")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="students not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="students not found"
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

    public function getStudentById($id){
        $student = StudentModel::find($id);
        if (!$student) {
            $response = [
                'message' => 'failed',
                'errors' => 'student not found',
                'status' => 404,
                'data' => []
            ];
            return response()->json($response, 404);
        }

         

        
        $infoUser = Usermodel::find($student['user_id']);
        $infoUser->makeHidden(['password', 'created_at', 'updated_at','id_rol']);
    
        $infoCourse = CourseModel::find($student['course_id']);
    
        $studentData = [
            'id' => $student['id'],
            'user' => $infoUser,
            'course' => [
                'id' => $infoCourse->id,
                'name_course' => $infoCourse->name,
            ],
        ];
    
         
        
        
        $response = [
            'message' => 'success',
            'status' => 200,
            'data' => $studentData
        ];
        return response()->json($response, 200);

    }
    /**
     * @OA\Get(
     *     path="/api/students/courses/{id}",
     *     tags={"students"},
     *     summary="Retrieve all students by course ID",
     *     description="Retrieve all students enrolled in a specific course by the course ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the course",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of students enrolled in the course",
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
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john.doe@example.com")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Student not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="failed"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="string",
     *                 example="student not found"
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

    public function getStudentsByCourrseId($id){

        $students=StudentModel::where('course_id',$id)->get();
        
        if($students->isEmpty()){
            $response = [
                'message' => 'failed',
                'errors' => 'student not found',
                'status' => 404,
                'data' => []
            ];
            return response()->json($response, 404);
            }
        $studentsInfoData = [];

        foreach ($students as $student) {
            $infoUser = Usermodel::find($student['user_id']);
            $infoCourse=CourseModel::find($id);
            if ($infoUser) {
                $infoUser->makeHidden(['password', 'created_at', 'updated_at']);
            }

             

            $studentData = [
                'studen_id' => $student['id'],
                'user' => $infoUser,
                'course'=>$infoCourse,
                 
            ];

            array_push($studentsInfoData, $studentData);
        }

        $response = [
            'message' => 'success',
            'status' => 200,
            'data' => $studentsInfoData
        ];

        return response()->json($response, 200);
    }
    /**
     * @OA\Delete(
     *     path="/api/students/{id}",
     *     tags={"students"},
     *     summary="Delete a student by ID",
     *     description="Delete a student by their ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the student to be deleted",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="student deleted successfully"
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
     *         description="Student not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="student not found"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=404
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
     *                 property="errors",
     *                 type="string",
     *                 example="Error message here"
     *             )
     *         )
     *     )
     * )
     */
    public function deleteStudentById($id){
        try {
            $student = StudentModel::find($id);
    
            if (!$student) {
                $response = [
                    'message' => 'student not found',
                    'status' => 404,
                ];
                return response()->json($response, 404);
            }
    
            $student->delete();
    
            $response = [
                'message' => 'student deleted successfully',
                'status' => 200,
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'message' => 'internal server error',
                'status' => 500,
                'errors' => $e->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }


}
