<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Models\Usermodel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RetrievePassword;

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

 

class AuthController extends Controller
{
        /**
         * @OA\Post(
         *     path="/api/users",
         *     summary="Register a new user",
         *     tags={"authentication"},
         *     @OA\RequestBody(
         *         required=true,
         *         description="User details",
         *         @OA\JsonContent(
         *             required={"name", "email", "id_rol", "password"},
         *             @OA\Property(property="name", type="string", example="John Doe"),
         *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
         *             @OA\Property(property="id_rol", type="integer", example=2),
         *             @OA\Property(property="password", type="string", example="password123")
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="User created successfully",
         *        
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
         *             @OA\Property(property="errors", type="object", example={"error message"}),
         *             @OA\Property(property="status", type="integer", example=500),
         *             @OA\Property(property="data", type="object", example={})
         *         )
         *     )
         * )
         */
    public function register(Request $request)

    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'id_rol' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                'status' => 400,
                'data' => []
            ], 400);
        }

        try {
            $user = Usermodel::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'id_rol' => $request->input('id_rol'),
                'password' => bcrypt($request->input('password'))
            ]);

            $accessToken = $user->createToken('authToken')->plainTextToken;

            $data = [
                'message' => 'success',
                'status' => 201,
                'data' => $request->all(),
            ];

            return response()->json($data)->header('Authorization', 'Bearer ' . $accessToken);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating user',
                'errors' => $e->getMessage(),
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
    public function login(Request $request){
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;
            $roles = [
                1 => "admin",
                2 => "teacher",
                3 => "student"
            ];
            $rolNombre = $roles[$user->id_rol] ?? 'Unknown';
            $response = [
                'message' => 'success',
                'status' => 200,
                'rold' => $rolNombre,
                'data' => $request->all()
            ];

            return response()->json($response)->header('Authorization', 'Bearer '  . $token);

        } else {
            $response = [
                'message' => 'failed',
                'error' => 'incorrects credentials',
                'status' => 401,
            ];

            return response()->json($response, 401);
        }
}

        /**
         * @OA\Post(
         *     path="/api/password-refresh",
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
        public function passwordRefresh(Request $request)
        {
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

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        $lengthpassword = strlen($chars);
        $new_password = '';

        for ($i = 0; $i < 24; $i++) {
            $index = rand(0, $lengthpassword - 1);
            $new_password .= $chars[$index];
        }

        $user->password = bcrypt($new_password);
        $user->save();

        try {
            Mail::to($user->email)->send(new RetrievePassword($user->name,$new_password));
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
            'new_password' => $new_password
        ]);
        }
    

}
