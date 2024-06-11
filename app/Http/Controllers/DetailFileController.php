<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\DetailFileModel;
use App\constans\ResponseManager;
use App\Models\ActivityFileModel;
use App\Models\ActivityModel;
use App\Models\StudentModel;

class DetailFileController extends Controller{
    protected $responseManager;

    public function __construct(ResponseManager $responseManager)
    {
        $this->responseManager=$responseManager;
    }
    /**
     * @OA\Post(
     *     path="file_detail/{activityFileId}/by/{studentId}",
     *     tags={"DetailFilesActivities"},
     *     summary="Check if a student has already checked a file activity and register the check if not.",
     *     @OA\Parameter(
     *         name="activityFileId",
     *         in="path",
     *         description="ID of the file activity to check",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         description="ID of the student",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The file activity check has been created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="created", description="Success message."),
     *             @OA\Property(property="status", type="integer", example=201, description="HTTP status code."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="activity_file_id", type="integer", example="1", description="ID of the activity file."),
     *                  @OA\Property(property="student_id", type="integer", example="1", description="ID of the student.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to failed validation or business logic error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="bad request", description="Error message."),
     *             @OA\Property(property="error", type="string", example="student or file activity not found", description="Validation or business logic error."),
     *             @OA\Property(property="status", type="integer", example=400, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error while processing the request.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="internal server error", description="Error message."),
     *             @OA\Property(property="error", type="string", description="Description of the internal error."),
     *             @OA\Property(property="status", type="integer", example=500, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function checkFileActivityByStudentId($activityFileId,$studentId){

        $student=StudentModel::find($studentId);
        $fileActicity=ActivityFileModel::find($activityFileId);

        if(!$student || !$fileActicity){
            $response=$this->responseManager->badRequest('student or file activity not found');
            return response()->json($response,400);
        }
        $activity=ActivityModel::find($fileActicity->activity_id);
        if ($student->course_id != $activity->course_id ){
            $response=$this->responseManager->badRequest('student not valid');
            return response()->json($response,400);
        }
        $studentIsCheckedFile=DetailFileModel::where('student_id',$studentId)
                                                        ->where('activity_file_id',$activityFileId)
                                                        ->get();
        if(!$studentIsCheckedFile->isEmpty()){
            $response=$this->responseManager->badRequest('student already check this file');
            return response()->json($response,400);
        }
        try{ 
            $detailFile=DetailFileModel::create([
                                        'activity_file_id'=>$activityFileId,
                                        'student_id'=>$studentId]);
            $response=$this->responseManager->created($detailFile);
            return response()->json($response,201);

        }catch(\Exception $e)
        {
            $response=$this->responseManager->serverError($e->getMessage());
            return response()->json($response,500);  
        }
       

    }
}
