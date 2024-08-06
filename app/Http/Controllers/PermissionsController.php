<?php

namespace App\Http\Controllers;

use App\constans\PermissionsList;
use App\constans\ResponseManager;
use App\Models\PermisionModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PermissionsController extends Controller{
    protected $responseManager;
    public function __construct(ResponseManager $responseManager){
        $this->responseManager=$responseManager;
    }
    /**
     * @OA\Post(
     *     path="/permissions",
     *     summary="Create a new permission",
     *     tags={"permissions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "permision_detail"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="admin"
     *                 ),
     *                 @OA\Property(
     *                     property="permision_detail",
     *                     type="string",
     *                     example="Permission details"
     *                 ),
     *                 @OA\Property(
     *                     property="expired_date",
     *                     type="string",
     *                     format="date-time",
     *                     example="04-09-2024 01:39"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permission successfully created",
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
     *                     property="name",
     *                     type="string",
     *                     example="admin"
     *                 ),
     *                 @OA\Property(
     *                     property="permision_detail",
     *                     type="string",
     *                     example="Permission details"
     *                 ),
     *                 @OA\Property(
     *                     property="expired_date",
     *                     type="string",
     *                     format="date-time",
     *                     example="2024-09-04 01:39:00"
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
    public function createPermission(Request $request){
        $listPermissions =PermissionsList::permissionsSchool();
        $permissionsString = implode(',', array_map('strtolower', $listPermissions));

        $validator=Validator::make($request->all(),[
             'name'=>'required|in:'.$permissionsString,
             'permision_detail'=>'required|string',
             'expired_date'=>'nullable|date_format:d-m-Y H:i',
        ]);
        if ($validator->fails()){
            $response=$this->responseManager->badRequest($validator->errors());
            return response()->json($response,400);
        }
        $expiredDate=null;

        if ($request->input('expired_date')){
            $expiredDate=Carbon::createFromFormat('d-m-Y H:i', $request->input('expired_date'));
        }
        try{
            $permission=PermisionModel::create([
                'name'=>$request->input('name'),
                'permision_detail'=>$request->input('permision_detail'),
                'expired_date'=>$expiredDate,
            ]);
            $response=$this->responseManager->created($permission);
            return response()->json($response,201);

        }catch(\Exception  $e){
            $response=$this->responseManager->serverError($e->getMessage());
            return response()->json($response,500);
        }
    }
        /**
     * @OA\Get(
     *     path="/permissions",
     *     summary="Retrieve all permissions",
     *     tags={"permissions"},
     *     @OA\Response(
     *         response=200,
     *         description="Permission retrieved successfully",
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
     *                     property="name",
     *                     type="string",
     *                     example="admin"
     *                 ),
     *                 @OA\Property(
     *                     property="permision_detail",
     *                     type="string",
     *                     example="Permission details"
     *                 ),
     *                 @OA\Property(
     *                     property="expired_date",
     *                     type="string",
     *                     format="date-time",
     *                     example="2024-09-04 01:39:00"
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
     *         response=404,
     *         description="Permission not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Permission not found"
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
    public function getAllPermissions(){
        $permissions=PermisionModel::get();
        if($permissions->isEmpty()){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);

        }
        $response=$this->responseManager->success($permissions);
        return response()->json($response,200);

    }
    /**
     * @OA\Get(
     *     path="/permissions/{id}",
     *     summary="Retrieve a specific permission",
     *     tags={"permissions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the permission to retrieve",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission retrieved successfully",
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
     *                     property="name",
     *                     type="string",
     *                     example="admin"
     *                 ),
     *                 @OA\Property(
     *                     property="permision_detail",
     *                     type="string",
     *                     example="Permission details"
     *                 ),
     *                 @OA\Property(
     *                     property="expired_date",
     *                     type="string",
     *                     format="date-time",
     *                     example="2024-09-04 01:39:00"
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
     *         response=404,
     *         description="Permission not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Permission not found"
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
    public function getPermissionById($id){
        $permission=PermisionModel::find($id);
        if(!$permission){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);

        }
        $response=$this->responseManager->success($permission);
        return response()->json($response,200);

    }
    /**
     * @OA\Post(
     *     path="/permissions/{id}/extend",
     *     summary="Extend the expiration date of a specific permission",
     *     tags={"permissions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the permission to extend",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"days_num"},
     *                 @OA\Property(
     *                     property="days_num",
     *                     type="integer",
     *                     example=30,
     *                     description="Number of days to extend the expiration date"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission expiration date successfully extended",
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
     *                     property="name",
     *                     type="string",
     *                     example="admin"
     *                 ),
     *                 @OA\Property(
     *                     property="permision_detail",
     *                     type="string",
     *                     example="Permission details"
     *                 ),
     *                 @OA\Property(
     *                     property="expired_date",
     *                     type="string",
     *                     format="date-time",
     *                     example="2024-10-04 01:39:00"
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
     *         response=404,
     *         description="Permission not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Permission not found"
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
    public function addDaysToPermision(Request $request,$id){
        $permission=PermisionModel::find($id);
        if (!$permission){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }
        $validator=Validator::make($request->all(),[
            'days_num'=>'required|integer'
        ]);
        if ($validator->fails()){
            $response=$this->responseManager->badRequest($validator->errors());
            return response()->json($response,400);
        }

        $daysNum = $request->input('days_num');
        $dateToUpdate=Carbon::now();

        if($permission->expired_date){
            $dateToUpdate = Carbon::parse($permission->expired_date);
        }
        $dateToUpdate->addDays($daysNum);
        try{
            $permisioUpdated=$permission->update([
                'expired_date'=> $dateToUpdate->format('Y-m-d H:i:s')

            ]);
            $response=$this->responseManager->success($permisioUpdated);
            return response()->json($response,200);
        }catch(\Exception $e){
            $response=$this->responseManager->serverError($e->getMessage());
            return response()->json($response,500);
        }

    }
    /**
     * Delete a specific permission
     *
     * @OA\Delete(
     *     path="/permissions/{id}",
     *     summary="Delete a specific permission",
     *     tags={"permissions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the permission to delete",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Permission successfully deleted"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Permission not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Permission not found"
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
    public function deletePermissionById($id) {
        
        $permission = PermisionModel::find($id);

        if (!$permission) {
            $response = $this->responseManager->notFound();
            return response()->json($response, 404);
        }
        try {
            $permission->delete();

            $response = $this->responseManager->delete('permission');
            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = $this->responseManager->serverError($e->getMessage());
            return response()->json($response, 500);
        }
    }

}
