<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Usermodel;


class UserController extends Controller{
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
         *                 @OA\Property(property="id_rol", type="integer", example=1),
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

        $data = Usermodel::select('id', 'name', 'email', 'id_rol' ,'created_at', 'updated_at')->get();

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
                'errors' => 'User not found'
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
     * @OA\Patch(
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
        
        if (!$user) {
            $data = [
                "message" => 'error',
                'status' => 404,
                'errors' => 'User not found'
            ];
            return response()->json($data, 404);    
        }
        $userdata=$request->all();
        if ($request->password){
            $userData['password'] = bcrypt($request->password);
        }
        
        try {
        
            $user->update($userData);
            
            $data = [
                "message" => 'success',
                'status' => 200,
                'data' => $request->all()
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






    