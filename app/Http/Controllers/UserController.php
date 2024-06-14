<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usermodel;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ImageManager;
use App\constans\ResponseManager;




class UserController extends Controller{

    protected $responseManager;

    public function __construct(ResponseManager $responseManager){
        $this->responseManager=$responseManager;
    }


    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"users"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="rol_id", type="integer", example=1),
     *                 @OA\Property(property="image_url", type="string", example="https://cloudinary?v237&"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="errors", type="string", example="data not found"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */

   
    public function get_users(){

        $data = Usermodel::select('id', 'name', 'email', 'rol_id' ,'created_at', 'updated_at','image_url')->get();

        if ($data->isEmpty()) {  
            $data = [
                'message' => 'error',
                'errors' => 'data not found',
                'status' => 404,
                'data' => []
            ];
            return response()->json($data, 404);
        }
        
        return response()->json($data, 200);  
        
                }
                
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user by ID",
     *     tags={"users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to retrieve",
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
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="id_rol", type="integer", example=1),
     *                 @OA\Property(property="image_url", type="string", example="hhtps://coudnary/v?qdsj"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="errors", type="string", example="User not found")
     *         )
     *     )
     * )
     */


    public function getUser($id){
        $user = Usermodel::find($id);

        if (!$user) {
            $data = [
                "message" => 'error',
                'status' => 404,
                'error' => 'User not found'
            ];
            return response()->json($data,404);
        }
        unset($user->password);
        $data = [
            "message" => 'success',
            'status' => 200,
            'data'=>$user
        ];
        return response()->json($data,200);
        }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete user by ID",
     *     tags={"users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="errors", type="string", example="User not found")
     *         )
     *     )
     * )
     */

 
    public function deleteUser($id){
        $user=Usermodel::find($id);
        
        if(!$user){
            $data = [
                "message" => 'error',
                'status' => 404,
                'errors' => 'User not found'
            ];
            return response()->json($data,404);
        }
        
        $user->delete();
        $data = [
            "message" => 'users was deleted',
            'status' => 200,
                
        ];
            return response()->json($data,200);
    
    
        }
    
    /**
     * @OA\Post(
     *     path="/api/users/{id}",
     *     summary="Update user by ID",
     *     tags={"users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data to update",
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},   
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="id_rol", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="errors", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */




    public function updateUser($id, Request $request){
        $user = Usermodel::find($id);

        $validator =Validator::make($request->all(),[
            'password'=>'prohibited|string|min:8',
            'image_url'=>'prohibited',
            'email'=>'prohibited'
        ]);

        if($validator->fails()){
            $response=$this->responseManager->badRequest($validator->errors());
            return response()->json($response,400);     
        }
        
        if (!$user) {
            $data = [
                "message" => 'error',
                'status' => 404,
                'errors' => 'User not found'
            ];
            return response()->json($data, 404);    
        }
        $userData=$request->all();
        try {
        
            $user->update($userData);
            
            $data = [
                "message" => 'success',
                'status' => 200,
                'data' => $userData
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
    /**
     * @OA\Patch(
     *     path="/api/users/{id}/change_image",
     *     summary="Change the user's image",
     *     tags={"users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="The image file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="string", example="https://cloudinary?v237&")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="bad request"),
     *             @OA\Property(property="error", type="object"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="user not found"),
     *             @OA\Property(property="error", type="string", example="User not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="error"),
     *             @OA\Property(property="error", type="string", example="Error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */


    public function changeImageUser($id, Request $request) {
         
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'bad request',
                'error' => $validator->errors(),
                'status' => 400,
                'data' => $request->all()
            ], 400);
        }

        $user = Usermodel::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'user not found',
                'error' => 'User not found',
                'status' => 404,
                'data' => []
            ], 404);
        }

        try {
            $imageManager = new ImageManager($request->file('image'));
            $newUrlImage = $imageManager->changeImage($user->image_url);

            $user->update(['image_url'=>$newUrlImage]);
            return response()->json([
                'message' => 'success',
                'status' => 200,
                'data' => $newUrlImage
            ], 200);



        } catch (\Exception $e) {
            return response()->json([
                'message' => 'error',
                'error' => $e->getMessage(),
                'status' => 500,
                'data' => []
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/change_password",
     *     summary="Change the password of a user",
     *     tags={"users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="New password",
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string", example="newsecurepassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password changed successfully"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="rol_id", type="integer", example=2), 
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-13T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-13T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function changePasswordByUserId($id, Request $request){

        $user=Usermodel::find($id);
        $validator =Validator::make($request->all(),[
            'password'=>'required|string|min:8'
        ]);

        if($validator->fails()){
            $response=$this->responseManager->badRequest($validator->errors());
            return response()->json($response,400);     
        }

        if (!$user){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }

        try{
            $user->update(['password' => bcrypt($request->input('password'))]);
            $response=$this->responseManager->success($user);
            return response()->json($response,200);
        }catch(\Exception $e){
            $response=$this->responseManager->serverError($e->getMessage());
            return response()->json($response,400);
        }

    }
}






    