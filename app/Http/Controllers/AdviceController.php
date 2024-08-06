<?php

namespace App\Http\Controllers;

use App\Models\AdviceModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ImageManager;
use App\Models\PermisionModel;

class AdviceController extends Controller{
    /**
     * @OA\Post(
     *     path="/api/advices",
     *     tags={"advices"},
     *     summary="Create a new advice",
     *     description="Create a new advice with optional image upload.",
     *     operationId="createAdvice",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Advice data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"text"},
     *                 @OA\Property(property="text", type="string", example="This is an advice text."),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Advice created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                    @OA\Property(property="text", type="string", example="bienvenidos"),
     *                    @OA\Property(property="image_url", type="string", example="https://clodinary?v?123"),
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="bad request"),
     *             @OA\Property(property="error", type="object"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object",example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error creating advice"),
     *             @OA\Property(property="error", type="string", example="Internal server error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object",example={})
     *         )
     *     )
     * )
     */

    public function CreateAdvice(Request $request){
        $validator=Validator::make($request->all(),[
            'text'=>'required|string',
            'image'=>'nullable|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        if ($validator->fails()){
           $response=[
            'messagge'=>'bad request',
            'error'=>$validator->errors(),
            'status'=>400,
            'data'=>[]
           ];
           return response()->json($response,400);
        }
        try {
            
            $imageManager = new ImageManager($request->file('image'));
            $imagePath = PermisionModel::where("name","storage")->exists()
                        ?$imageManager->saveImage()
                        :$imageManager->saveImageInLocal();

            

    
        $advice = AdviceModel::create([
            'text' => $request->input('text'),
            'image_url' => $imagePath
        ]);

        
        $data = [
            'message' => 'success',
            'status' => 201,
            'data' =>$advice
        ];
        
        
        return response()->json($data, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
                'status' => 500,
                'data' => $imagePath 
            ], 500);
        }
        }

    /**
     * @OA\Get(
     *     path="/api/advices",
     *     tags={"advices"},
     *     summary="Get all advices",
     *     description="Retrieve a list of all advices.",
     *     operationId="getAllAdvices",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with a list of advices",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="text", type="string", example="This is an advice text."),
     *                     @OA\Property(property="image_url", type="string", example="http://example.com/images/advice1.jpg"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-08T05:21:34.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-08T05:21:34.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advices not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="advices not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={}))
     *         )
     *     )
     * )
     */
    public function getAllAdvices(){
        $advices=AdviceModel::all();
        if($advices->isEmpty()){
            $response=[
                'message'=>'failed',
                'error'=>'advices not found',
                'satus'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);
        }
        if (!PermisionModel::where("name", "storage")->exists()){
            foreach ($advices as $advice) {
                try{
                    $imageManager = new ImageManager(null);
                    $imageContent = $imageManager->getImageFromLocal($advice->image_url);
                    $advice->image_url = $imageContent;
                }catch(\Exception $e){

                }
                
            }
        }
        $response=[
            'message'=>'succes',
            'satus'=>200,
            'data'=>$advices
        ];
        return response()->json($response,200);

    }
    /**
     * @OA\Delete(
     *     path="/api/advices/{id}",
     *     tags={"advices"},
     *     summary="Delete an advice by ID",
     *     description="Delete a specific advice by its ID.",
     *     operationId="deleteAdviceById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the advice to be deleted",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advice deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advice not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="advice not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="Internal server error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={}))
     *         )
     *     )
     * )
     */

    public function deleteAdviceById($id){
        $advice=AdviceModel::find($id);
        if(!$advice){
            $response=[
                'message'=>'failed',
                'error'=>'advice not found',
                'status'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);
        }
        try{
            $advice->delete();
            $response=[
                'message'=>'succes',
                'status'=>200
            ];
            return response()->json($response,200);
        }
        catch(\Exception $e){
            $response=[
                'message'=>'failed',
                'error'=>$e->getMessage(),
                'status'=>500,
                'data'=>[]
            ];
            return response()->json($response,500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/advices/{Id}",
     *     tags={"advices"},
     *     summary="Update an advice by ID",
     *     description="Update a specific advice by its ID.",
     *     operationId="updateAdviceById",
     *     @OA\Parameter(
     *         name="adviceId",
     *         in="path",
     *         required=true,
     *         description="ID of the advice to be updated",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Advice data to be updated",
     *         @OA\JsonContent(
     *             required={"text"},
     *             @OA\Property(property="text", type="string", example="Updated advice text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advice updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="text", type="string", example="bienvenidos"),
     *                 @OA\Property(property="image_url", type="string", example="https://cloudinary?v=123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="bad request"),
     *             @OA\Property(property="error", type="object"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advice not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="advice not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="internal server error"),
     *             @OA\Property(property="error", type="string", example="Internal server error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */


    public function updateAdviceById($id, Request $request){
        $validator=Validator::make($request->all(),[
            'text'=>'nullable|text',
        ]);

        if ($validator->fails()){
            $response=[
             'messagge'=>'bad request',
             'error'=>$validator->errors(),
             'status'=>400,
             'data'=>[]
            ];
            return response()->json($response,400);
         }
        $advice=AdviceModel::find($id);
        
        if(!$advice){
            $response=[
                'messagge'=>'failed',
                'error'=>'advice not found',
                'status'=>404,
                'data'=>[]
               ];
               return response()->json($response,400);
        }
        try{
            $advice->update($request->all());
            $response=[
                'messagge'=>'success',
                'status'=>200,
                'data'=>AdviceModel::find($id)
               ];
               return response()->json($response,200);
        }
        catch(\Exception $e){
            $response=[
                'messagge'=>'internal server error',
                'error'=>$e->getMessage(),
                'status'=>404,
                'data'=>[]
               ];
               return response()->json($response,500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/advices/{adviceId}/change_image",
     *     tags={"advices"},
     *     summary="Change the image of an advice by ID",
     *     description="Update the image of a specific advice by its ID.",
     *     operationId="changeImageFromAdviceById",
     *     @OA\Parameter(
     *         name="adviceId",
     *         in="path",
     *         required=true,
     *         description="ID of the advice to update the image",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="New image for the advice",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="image",
     *                     description="Image file to upload",
     *                     type="file",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="image_url", type="string", example="https://cloudinary?v=123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="bad request"),
     *             @OA\Property(property="error", type="object"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advice not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="advice not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="internal server error"),
     *             @OA\Property(property="error", type="string", example="Internal server error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function changeImageFromAdviceById($id, Request $request){
        
        $validator=Validator::make($request->all(),[
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()){
            $response=[
             'messagge'=>'bad request',
             'error'=>$validator->errors(),
             'status'=>400,
             'data'=>[]
            ];
            return response()->json($response,400);
         }
        $advice=AdviceModel::find($id);
        
        if(!$advice){
            $response=[
                'messagge'=>'failed',
                'error'=>'advice not found',
                'status'=>404,
                'data'=>[]
               ];
               return response()->json($response,400);
        }
        try{
            $imageManager=new ImageManager($request->file('image'));
            $newImagePath=PermisionModel::where("name","storage")->exists()
                            ?$imageManager->changeImage($advice->image_url)
                            :$imageManager->changeImageInLocal($advice->image_url);
            
            $advice->update(['image_url'=>$newImagePath]);
            
            $response=[
                'messagge'=>'success',
                'status'=>200,
                'data'=>AdviceModel::find($id)
               ];
               return response()->json($response,200);
        }
        catch(\Exception $e){
            $response=[
                'messagge'=>'internal server error',
                'error'=>$e->getMessage(),
                'status'=>500,
                'data'=>[]
               ];
               return response()->json($response,500);
        }
    }
}

