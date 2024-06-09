<?php

namespace App\Http\Controllers;

use App\Models\ActivitiesComentsModel;
use App\Models\ActivityModel;
use App\Models\CourseModel;
use App\Models\StudentModel;
use App\Models\Usermodel;
use App\Mappers\CommetsMapper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ActivitiescomentsController extends Controller{   
    /**
     * @OA\Post(
     *     path="/api/comments",
     *     tags={"comments"},
     *     summary="Create a new comment",
     *     description="Create a new comment for a specific activity by a user.",
     *     operationId="createComment",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Comment data",
     *         @OA\JsonContent(
     *             required={"activity_id", "user_id", "comment"},
     *             @OA\Property(property="activity_id", type="integer", example=1, description="ID of the activity"),
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user"),
     *             @OA\Property(property="comment", type="string", example="This is a comment", description="The comment text")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="created"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1, description="ID of the comment"),
     *                 @OA\Property(property="activity_id", type="integer", example=1, description="ID of the activity"),
     *                 @OA\Property(property="user_id", type="integer", example=1, description="ID of the user"),
     *                 @OA\Property(property="comment", type="string", example="This is a comment", description="The comment text"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-07T12:34:56Z", description="Creation timestamp"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-07T12:34:56Z", description="Last update timestamp")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="error", type="string",example="activity_id is required"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *   
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
    public function createComment(Request $request) {
        $validator = Validator::make($request->all(), [
            'activity_id' => 'required|exists:activities,id',
            'user_id' => 'required|exists:users,id',
            'comment' => 'required|string'
        ]);
    
        if ($validator->fails()) {
            $response = [
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }
    
        $user = UserModel::find($request->input('user_id'));
        $activity = ActivityModel::find($request->input('activity_id'));
    
        $isStudent = StudentModel::where('user_id', $user->id)->pluck('course_id')->contains($activity->course_id);
        $isTeacher = CourseModel::where('teacher_id', $user->id)->pluck('id')->contains($activity->course_id);
    
        if (!$isStudent &&  !$isTeacher) {
            $response = [
                'message' => 'bad request',
                'error' => 'user not valid',
                'status' => 400,
                'data' => []
            ];
            return response()->json($response, 400);
        }
    
        try {
            $comment = ActivitiesComentsModel::create([
                'activity_id' => $request->input('activity_id'),
                'user_id' => $request->input('user_id'),
                'comment' => $request->input('comment')
            ]);
            $response = [
                'message' => 'created',
                'status' => 201,
                'data' => $comment
            ];
            return response()->json($response, 201);
        } catch (\Exception $e) {
            $response = [
                'message' => 'internal server error',
                'error' => $e->getMessage(),
                'status' => 500,
                'data' => []
            ];
            return response()->json($response, 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/comments/activity/{id}",
     *     summary="Get all comments for a specific activity",
     *     tags={"comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the activity",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="comment", type="string", example="Great activity!"),
     *                     @OA\Property(property="user_name", type="string", example="John Doe"),
     *                     @OA\Property(property="rol", type="string", example="student"),
     *                     @OA\Property(property="date", type="string", format="date-time", example="2023-06-08T14:12:33Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comments not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="comments not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function getAllCommentFromActivityId($id){
        $comments=ActivitiesComentsModel::where('activity_id',$id)->get();

        if($comments->isEmpty()){
            $response=[
                'message'=>'failed',
                'error'=>'commetns not fount',
                'status'=>404,
                'data'=>[]
            ];
            return response()->json($response,404);
        }
        
        $commentMapper=new CommetsMapper($comments);
        $commentsMapped=$commentMapper->mapperCommetsForAtivity();

        $response=[
            'massage'=>'success',
            'status'=>200,
            'data'=>$commentsMapped
        ];
        return response()->json($response,200);
    }
    
}
