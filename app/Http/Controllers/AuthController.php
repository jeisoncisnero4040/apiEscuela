<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Usermodel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RetrievePassword;
use App\Helpers\ImageManager;
use App\Helpers\PasswordGenerator;
use Illuminate\Support\Facades\DB;
use App\constans\ResponseManager;

/**
 * @OA\Info(
 *     title="APIhidalgo.devs",
 *     version="1.0",
 *     description="Api escuela hidalgo",
 *     termsOfService="https://example.com/terms",
 *     @OA\Contact(
 *         email="msolegario.cisneros@gmail.com",
 *         name="Equipo de soporte"
 *     ),
 *     @OA\License(
 *         name="Licencia",
 *         url="https://example.com/license"
 *     )
 * )
 *
 * @OA\Server(url="http://por_definir/api/documentation")
 */

 

class AuthController extends Controller{

    protected $responseManager;

    public function __construct(ResponseManager $responseManager)
    {
        $this->responseManager=$responseManager;
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Register a new user",
     *     tags={"authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User details",
     *         @OA\JsonContent(
     *             required={"name", "email", "rol_id", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="rol_id", type="integer", example=2),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="rol_id", type="integer", example=2),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="image_url", type="string", example="https://cloudfare?media01")
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
     *         response=500,
     *         description="Error creating user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating user"),
     *             @OA\Property(property="error", type="object", example={"error message"}),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'rol_id' => 'required|exists:rols,id',
            'password' => 'required|min:8',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'error' => $validator->errors(),
                'status' => 400,
                'data' => []
            ], 400);
        }

        
        try {
            
            $imageManager = new ImageManager($request->file('image'));
            $imagePath = $imageManager->saveImage();

            

    
        $user = Usermodel::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'rol_id' => $request->input('rol_id'),
            'password' => bcrypt($request->input('password')),
            'image_url' => $imagePath
        ]);

        
        $accessToken = $user->createToken('authToken')->plainTextToken;

        unset($user->password);
        $data = [
            'message' => 'success',
            'status' => 201,
            'data' => [
                'user' => $user,    
                'token' => $accessToken
            ],
        ];
        
        
        return response()->json($data, 201)->header('Authorization', 'Bearer ' . $accessToken);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
                'status' => 500,
                'data' => []
            ], 500);
        }
}
        

        /**
         * @OA\Post(
         *     path="/api/login",
         *     summary="Login a user",
         *     tags={"authentication"},
         *     @OA\RequestBody(
         *         required=true,
         *         description="User credentials",
         *         @OA\JsonContent(
         *             required={"email", "password"},
         *             @OA\Property(property="email", type="string", format="email", example="example@example.com"),
         *             @OA\Property(property="password", type="string", format="password", example="password123")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="User logged in successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="success"),
         *             @OA\Property(property="status", type="integer", example=200),
         *             @OA\Property(property="role", type="string", example="admin"),
         *             @OA\Property(property="data", type="object", example={"email": "example@example.com"})
         *         )
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Unauthorized",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="failed"),
         *             @OA\Property(property="error", type="string", example="incorrect credentials"),
         *             @OA\Property(property="status", type="integer", example=401)
         *         )
         *     )
         * )
         */
        public function login(Request $request) {
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ]);
        
             
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'error' => $validator->errors(),
                    'status' => 400,
                ], 400);
            }
        
            $credentials = $request->only('email', 'password');
        
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('authToken')->plainTextToken;
                $roles = [
                    1 => "admin",
                    2 => "teacher",
                    3 => "student"
                ];
                $rolNombre = $roles[$user->rol_id] ?? 'Unknown';
                
                if ($rolNombre!="teacher"){
                    $user=$request->input('email');
                }
                $response = [
                    'message' => 'success',
                    'status' => 200,
                    'rol' => $rolNombre,
                    'data' => ['user'=>$user,
                                'password'=>bcrypt($request->input('password'))]
                ];
        
                return response()->json($response)->header('Authorization', 'Bearer ' . $token);
            } else {
                $response = [
                    'message' => 'failed',
                    'error' => 'incorrect credentials',
                    'status' => 401,
                ];
        
                return response()->json($response, 401);
            }
        }

        /**
         * @OA\Post(
         *     path="/api/retrieve_password",
         *     summary="Refresh user's password",
         *     tags={"authentication"},
         *     @OA\RequestBody(
         *         required=true,
         *         description="User email",
         *         @OA\JsonContent(
         *             required={"email"},
         *             @OA\Property(property="email", type="string", format="email", example="example@example.com")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="New password sent successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="success"),
         *             @OA\Property(property="status", type="integer", example=200),
         *             @OA\Property(property="new_password", type="string", example="newPassword123")
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="User not found",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="failed"),
         *             @OA\Property(property="error", type="string", example="user not found"),
         *             @OA\Property(property="status", type="integer", example=404)
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Error sending email",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="failed"),
         *             @OA\Property(property="error", type="string", example="error message"),
         *             @OA\Property(property="status", type="integer", example=500)
         *         )
         *     )
         * )
         */
    public function passwordRefresh(Request $request){
    
    $email = $request->only('email');
    $user = Usermodel::where('email', $email)->first();

    if (!$user) {
        return response()->json([
            'message' => 'failed',
            'error' => 'user not found',
            'status' => 404,
            'data' => $email
        ], 404);
    }
    $passwordGenerator=new PasswordGenerator();
    $newPassword=$passwordGenerator->generatePassword();


    $user->password = bcrypt($newPassword);
    $user->save();

    try {
        Mail::to($user->email)->send(new RetrievePassword($user->name,$newPassword));
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'failed',
            'error'=> $e->getMessage(),
            'status' => 500,
            'data' => $user->email
        ], 500);
    }

    return response()->json([
        'message' => 'success',
        'status' => 200,
        'new_password' => $newPassword
    ]);
    }
    
    
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=304,
     *         description="User not logged in",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not logged in"),
     *             @OA\Property(property="error", type="string", example="user not logged"),
     *             @OA\Property(property="status", type="integer", example=304),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token format",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bad Request"),
     *             @OA\Property(property="error", type="string", example="invalid token"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    
    public function logout(Request $request)
    {
        
        $token = $request->header('Authorization');
    
        
        if (!$token) {
            $data = [
                'message' => 'failed',
                'error' => 'user not logged',
                'status' => 304,
                'data' => []
            ];
            return response()->json($data, 304);
        }
    
         
        $tokenSplit = explode(' ', $token);
    
        
        if (count($tokenSplit) != 2 || $tokenSplit[0] !== 'Bearer') {
            $response=$this->responseManager->badRequest('invalid token');
            return response()->json($response, 400);
        }
    
 
        $tokenID = explode('|', $tokenSplit[1])[0];
    
 
        DB::table('personal_access_tokens')->where('id', (int)$tokenID)->delete();
        $request->headers->remove('Authorization');
    
 
        $response = $this->responseManager->success($request);
        return response()->json($response, 200);
    }
}