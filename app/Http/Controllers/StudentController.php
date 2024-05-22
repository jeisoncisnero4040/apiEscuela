<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\StudentModel;
use App\Models\CourseModel;

class StudentController extends Controller
{
    public function createStudent(Request $request)
    {
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
}
