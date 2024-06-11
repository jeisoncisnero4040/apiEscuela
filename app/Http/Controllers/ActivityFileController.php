<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\FileManager;
use App\Models\ActivityFileModel;
use App\Mappers\FilesMapper; 
use App\constans\ResponseManager;
use App\Models\ActivityModel;
use App\Models\StudentModel;

class ActivityFileController extends Controller{
    
    protected $responseManager;

    public function __construct(ResponseManager $responseManager) {
        $this->responseManager = $responseManager;
    }

    /**
     * @OA\Post(
     *     path="/api/files_activity",
     *     tags={"FilesActivities"},
     *     summary="Add a file or URL to an activity.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"activity_id", "description"},
     *             @OA\Property(property="activity_id", type="integer", example="1", description="ID of the activity to which the file or URL will be added."),
     *             @OA\Property(property="url", type="string", format="url", nullable=true, example="https://example.com/file.pdf", description="URL of the file to add. This field is optional and can be null."),
     *             @OA\Property(property="file", type="string", format="binary", nullable=true, description="File to add. This field is optional and can be null. Only provide this if no URL is provided."),
     *             @OA\Property(property="description", type="string", example="Description of the file", description="Description of the file to add.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The file or URL has been added successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="created", description="Success message."),
     *             @OA\Property(property="status", type="integer", example=201, description="HTTP status code."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="activity_id", type="integer", example="1", description="ID of the activity to which the file or URL will be added."),
     *                  @OA\Property(property="url", type="string", format="url", nullable=true, example="https://example.com/file.pdf", description="URL of the file to add. This field is optional and can be null."),
     *                  @OA\Property(property="description", type="string", example="Description of the file", description="Description of the file to add.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to failed validation or business logic error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="bad request", description="Error message."),
     *             @OA\Property(property="error", type="object", description="Validation or business logic errors."),
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
    public function addFileOrUrlToActivity(Request $request){
        $validator=Validator::make($request->all(),[
            'activity_id'=>'required|exists:activities,id',
            'url'=>'nullable|url',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:3072',
            'description'=>'required|string',

        ]);
        if($validator->fails()){
            $response=$this->responseManager->badRequest($validator->errors());
            return response()->json($response,400);
        }

        $url=$request->input('url');
        $file=$request->file('file');
        if(!$url && !$file || $url && $file){

            $response=$this->responseManager->badRequest('unique file or url valid');
            return response()->json($response,400);
        }
        
        try{
            if ($file){
                $filemanager=new FileManager();
                $url=$filemanager->saveFile($file);
            }
            $fileActivity=ActivityFileModel::create([
                'activity_id'=>$request->input('activity_id'),
                'file_url'=>$url,
                'description'=>$request->input('description')
            ]);
            $response=$this->responseManager->created($fileActivity);
            return response()->json($response,201);


        }catch(\Exception $e){
            $response=$this->responseManager->serverError($e->getMessage());
            return response()->json($response,500);
        }


    }
    /**
     * @OA\Get(
     *     path="/api/files_activity/activity/{id}",
     *     tags={"FilesActivities"},
     *     summary="Get all files for a specific activity.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the activity to get the files for."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of files for the specified activity.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success", description="Success message."),
     *             @OA\Property(property="status", type="integer", example=200, description="HTTP status code."),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1", description="File ID."),
     *                     @OA\Property(property="activity_id", type="integer", example="1", description="Activity ID."),
     *                     @OA\Property(property="file_url", type="string", format="url", example="https://example.com/file.pdf", description="URL of the file."),
     *                     @OA\Property(property="description", type="string", example="Description of the file", description="Description of the file."),
     *                     @OA\Property(property="type_file", type="string", example="pdf", description="file type")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found or no files associated with the activity.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", example="not found", description="Error details."),
     *             @OA\Property(property="status", type="integer", example=404, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error while processing the request.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", description="Description of the internal error."),
     *             @OA\Property(property="status", type="integer", example=500, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function getAllFilesByActivityId($id){

        $filesFromActivity=ActivityFileModel::where('activity_id',$id)->get();
        
        if($filesFromActivity->isEmpty()){

            $response=$this->responseManager->notFound();
            return response() ->json($response,404);
        }
        $filesMapper=new FilesMapper($filesFromActivity);
        $filesMapped=$filesMapper->mapperFilesFromActivity();

        $response=$this->responseManager->success($filesMapped);
        return response() ->json($response,200);


    }
    /**
     * @OA\Get(
     *     path="'files_activity/{activityId}/by/{studentId}",
     *     tags={"FilesActivities"},
     *     summary="Get all files related to a specific activity for a student.",
     *     @OA\Parameter(
     *         name="activityId",
     *         in="path",
     *         description="ID of the activity",
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
     *         response=200,
     *         description="Successful operation. Returns the files related to the activity for the student.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success", description="Success message."),
     *             @OA\Property(property="status", type="integer", example=200, description="HTTP status code."),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1", description="ID of the file."),
     *                     @OA\Property(property="activity_id", type="integer", example="1", description="Activity ID."),
     *                     @OA\Property(property="file_url", type="string", example="https://example.com/file.pdf", description="URL of the file."),
     *                     @OA\Property(property="description", type="string", example="Description of the file", description="Description of the file."),
     *                     @OA\Property(property="type_file", type="string", example="pdf", description="file type"),
     *                     @OA\Property(property="was cheked", type="boolean", example="true", description="was cheeknd by student")
     *                 )
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
     *         response=404,
     *         description="No files found for the activity.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", example="not found", description="Error description."),
     *             @OA\Property(property="status", type="integer", example=404, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function getAllFilesForActivityByStudent($activityId,$studentId){
        $student=StudentModel::find($studentId);
        

        if(!$student){
            $response=$this->responseManager->badRequest('student or file activity not found');
            return response()->json($response,400);
        }
        $activity=ActivityModel::find($activityId);
        if ($student->course_id != $activity->course_id ){
            $response=$this->responseManager->badRequest('student not valid');
            return response()->json($response,400);
        }

        $filesFromActivity=ActivityFileModel::where('activity_id',$activityId)->get();
        
        if($filesFromActivity->isEmpty()){

            $response=$this->responseManager->notFound();
            return response() ->json($response,404);
        }
        $filesMapper=new FilesMapper($filesFromActivity);
        $filesMapped=$filesMapper->mapperFilesForStudent($studentId);

        $response=$this->responseManager->success($filesMapped);
        return response() ->json($response,200);

    }

    /**
     * @OA\Get(
     *     path="/api/files_activity/{id}",
     *     tags={"FilesActivities"},
     *     summary="Get a file by his id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the activity to get the files for."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of files for the specified activity.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success", description="Success message."),
     *             @OA\Property(property="status", type="integer", example=200, description="HTTP status code."),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1", description="File ID."),
     *                     @OA\Property(property="activity_id", type="integer", example="1", description="Activity ID."),
     *                     @OA\Property(property="file_url", type="string", format="url", example="https://example.com/file.pdf", description="URL of the file."),
     *                     @OA\Property(property="description", type="string", example="Description of the file", description="Description of the file."),
     *                     @OA\Property(property="type_file", type="string", example="pdf", description="file type")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found or no files associated with the activity.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", example="not found", description="Error details."),
     *             @OA\Property(property="status", type="integer", example=404, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error while processing the request.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", description="Description of the internal error."),
     *             @OA\Property(property="status", type="integer", example=500, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function getActivityFileById($id){

        $fileActivity=ActivityFileModel::find($id);
        if (!$fileActivity){

            $response=$this->responseManager->notFound();
            
            return response()->json($response,404);   
        }
        $fileMapper=new FilesMapper($fileActivity);
        $fileMapped=$fileMapper->mapperFile();

        $response=$this->responseManager->success($fileMapped);
        return response() ->json($response,200);
    }
    /**
     * @OA\Delete(
     *     path="/api/files_activity/{id}",
     *     tags={"FilesActivities"},
     *     summary="Delete a file associated with an activity by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the file activity to delete."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The file activity has been deleted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="activityFile was deleted successfully", description="Success message."),
     *             @OA\Property(property="status", type="integer", example=200, description="HTTP status code.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File activity not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", example="not found", description="Error details."),
     *             @OA\Property(property="status", type="integer", example=404, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error while trying to delete the file.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", example="error trying delete file", description="Error details."),
     *             @OA\Property(property="status", type="integer", example=500, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function deleteFileActivityById($id){
        
        $fileActivity=ActivityFileModel::find($id);
        if (!$fileActivity){

            $response=$this->responseManager->notFound();
            
            return response()->json($response,404);   
        }
        $fileManager= new FileManager();
        $urlDeleted=$fileManager->deleteFile($fileActivity->file_url);
        if(!$urlDeleted){
            $response=$this->responseManager->serverError('error trying delete file');
            return response()->json($response,500);
        }
        $fileActivity->delete();


        $response=$this->responseManager->delete('activityFile');
        return response() ->json($response,200);
    }
    /**
     * @OA\Post(
     *     path="/api/files_activity/{id}",
     *     tags={"FilesActivities"},
     *     summary="Change the file or URL for a specific activity.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the file activity to update."
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="file", type="string", format="binary", nullable=true, description="New file to upload. This field is optional and can be null if a URL is provided."),
     *             @OA\Property(property="url", type="string", format="url", nullable=true, example="https://example.com/file.pdf", description="New URL to link. This field is optional and can be null if a file is provided.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The file or URL has been updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success", description="Success message."),
     *             @OA\Property(property="status", type="integer", example=200, description="HTTP status code."),
     *             @OA\Property(property="data", type="string",format="url", example="https://example.com/newfile.pdf", description="New URL of the file.")               
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to validation or business logic error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="bad request", description="Error message."),
     *             @OA\Property(property="error", type="object", description="Validation or business logic errors."),
     *             @OA\Property(property="status", type="integer", example=400, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File activity not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", example="not found", description="Error details."),
     *             @OA\Property(property="status", type="integer", example=404, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error while processing the request.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed", description="Error message."),
     *             @OA\Property(property="error", type="string", description="Description of the internal error."),
     *             @OA\Property(property="status", type="integer", example=500, description="HTTP status code."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function changueFileForFileActivityId($id,Request $request){
        $validator=Validator::make($request->all(),[
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:3072',
            'url'=> 'nullable|url'
        ]);
        if($validator->fails()){
            
            $response=$this->responseManager->badRequest($validator->errors());

            return response()->json($response,400);
        }
        $newUrl=$request->input('url');
        $newFile=$request->file('file');
        if(!$newUrl && !$newFile || $newUrl && $newFile){

            $response=$this->responseManager->badRequest('unique file or url valid');
            return response()->json($response,400);
        }

        $fileActivity=ActivityFileModel::find($id);
        if(!$fileActivity){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }
        try{
            if($newFile){
                $fileManager=new FileManager();
                $newUrl=$fileManager->changeFile($newFile,$fileActivity->file_url);
            }


            $fileActivity -> update(['file_url'=>$newUrl]);
            $response=$this->responseManager->success(['file_url'=>$newUrl]);
            
            return response()->json($response,200);
        }catch(\Exception $e){
            $response=$this->responseManager->serverError($e->getMessage());
            return response()->json($response,500);
        }
    }
}
