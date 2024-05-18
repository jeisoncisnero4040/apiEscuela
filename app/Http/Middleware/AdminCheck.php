<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usermodel;

class AdminCheck
{
    public function handle(Request $request, Closure $next)
    {
        $token=$request->header('Authorization');
        
        if (!$token){
            $data=[
                'message'=>'failed',
                'error'=>'user not logued',
                'status'=>304,
                'data'=>[]
            ];
            return response()->json($data,304);
            }
       
            $tokenSplit=explode(' ',$token);
            $tokenID=explode('|',$tokenSplit[1])[0];

        
            if (count($tokenSplit) !=2 || $tokenSplit[0] !='Bearer'){
            $data=[
                'message'=>'failed',
                'error'=>'invalid token',
                'status'=>304,
                'data'=>[]
            ];
            return response()->json($data,304);
            }
            $userId=DB::table('personal_access_tokens')
            ->where('id', (int)$tokenID)
            ->value('tokenable_id');
            

            if(!$userId || (Usermodel::find($userId)->id_rol !=1)){
                $data=[
                    'message'=>'failed',
                    'error'=>'Unauthorizate',
                    'status'=>403,
                    'data'=>Usermodel::find($userId)
                ];
                return response()->json($data,403);

            }
            
            return $next($request);
            
        }
        
        
        
    }

