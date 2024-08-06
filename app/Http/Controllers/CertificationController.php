<?php

namespace App\Http\Controllers;

use App\constans\ResponseManager;
use App\Mappers\AsciiMapper;
use App\Models\CertificationsModel;
use App\Models\StudentModel;
use App\Models\Usermodel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CertificationController extends Controller
{
    protected $responseManager;

    public function __construct(ResponseManager $responseManager){
        $this->responseManager = $responseManager;
    }
    /**
     * Create a new certification record
     *
     * @OA\Post(
     *     path="/certifications",
     *     summary="Create a new certification",
     *     tags={"certifications"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"student_id"},
     *                 @OA\Property(
     *                     property="student_id",
     *                     type="integer",
     *                     example=1,
     *                     description="ID of the student"
     *                 ),
     *                 @OA\Property(
     *                     property="numeric_calification",
     *                     type="number",
     *                     format="float",
     *                     example=8.5,
     *                     description="Numeric calification of the student"
     *                 ),
     *                 @OA\Property(
     *                     property="string_calification",
     *                     type="string",
     *                     example="B+",
     *                     description="String calification of the student"
     *                 ),
     *                 @OA\Property(
     *                     property="observations",
     *                     type="string",
     *                     example="Good performance",
     *                     description="Additional observations"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Certification successfully created",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="student_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="numeric_calification",
     *                     type="number",
     *                     format="float",
     *                     example=8.5
     *                 ),
     *                 @OA\Property(
     *                     property="string_calification",
     *                     type="string",
     *                     example="B+"
     *                 ),
     *                 @OA\Property(
     *                     property="observations",
     *                     type="string",
     *                     example="Good performance"
     *                 ),
     *                 @OA\Property(
     *                     property="certification",
     *                     type="string",
     *                     example="72101108108111328711111410810049"
     *                 ),
     *                 @OA\Property(
     *                     property="created_at",
     *                     type="string",
     *                     format="date-time",
     *                     example="2024-08-05 01:39:45"
     *                 ),
     *                 @OA\Property(
     *                     property="updated_at",
     *                     type="string",
     *                     format="date-time",
     *                     example="2024-08-05 01:39:45"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties={
     *                     "type": "array",
     *                     "items": {
     *                         "type": "string"
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Server error message"
     *             )
     *         )
     *     )
     * )
     */
    public function createCertification(Request $request){
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|numeric|exists:students,id',
            'numeric_calification' => 'nullable|numeric|between:0.0,10.0',
            'string_calification' => 'nullable|string',
            'observations' => 'nullable|string'
        ]);

        if ($validator->fails()){
            $response = $this->responseManager->badRequest($validator->errors());
            return response()->json($response, 400);
        }

        $numericCalification = $request->input('numeric_calification');
        $stringCalification = $request->input('string_calification');

        if ((!$numericCalification && !$stringCalification) || ($numericCalification && $stringCalification)) {
            $response = $this->responseManager->badRequest("Only string calification or numeric calification is valid");
            return response()->json($response, 400);
        }

        $student = StudentModel::find($request->input('student_id'));
        
        if (!$student) {
            $response = $this->responseManager->badRequest("Student not found");
            return response()->json($response, 400);
        }

        $email = UserModel::find($student->user_id)->email;
        $courseId = $student->course_id;

        $certificationNum = AsciiMapper::toAscii($email) . AsciiMapper::toAscii((string)$courseId);

        try {
            $certification = CertificationsModel::create([
                'student_id' => $request->input('student_id'),
                'numeric_calification' => $numericCalification,
                'string_calification' => $stringCalification,
                'observations' => $request->input('observations'),
                'certification' => $certificationNum
            ]);

            $response = $this->responseManager->created($certification);
            return response()->json($response, 201);
        } catch (\Exception $e) {
            $response = $this->responseManager->badRequest($e->getMessage());
            return response()->json($response, 500);
        }
    }
}
