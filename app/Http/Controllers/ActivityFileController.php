<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\FileManager;
use App\Models\ActivityFileModel;

class ActivityFileController extends Controller{

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
            $response=[
                'message'=>'bad request',
                'error'=>$validator->errors(),
                'status'=>400,
                'data'=>[]
            ];
            return response()->json($response,400);
        }

        $url=$request->input('url');
        $file=$request->file('file');
        if(!$url && !$file || $url && $file){
            $response=[
                'message'=>'bad request',
                'error'=>'only url or file is valid',
                'status'=>400,
                'data'=>[]
            ];
            return response()->json($response,400);
        }
        
        try{
            if ($file){
                $filemanager=new FileManager($file);
                $url=$filemanager->saveFile();
            }
            $fileActivity=ActivityFileModel::create([
                'activity_id'=>$request->input('activity_id'),
                'file_url'=>$url,
                'description'=>$request->input('description')
            ]);
            $response=[
                'message'=>'created',
                'satatus'=>201,
                'data'=>$fileActivity
            ];
            return response()->json($response,201);


        }catch(\Exception $e){
            $response=[
                'message'=>'internal server error',
                'error'=>$e->getMessage(),
                'status'=>500,
                'data'=>[]
            ];
            return response()->json($response,500);
        }


    }
}
