<?php
namespace App\constans;

class ResponseManager{

    public function badRequest($error){
        $response=[
            'message'=>'bad request',
            'error'=>$error,
            'status'=>400,
            'data'=>[]
        ];
        return $response;
    }

    public function notFound(){
        $response=[
            'message'=>'failed',
            'error'=>'not found',
            'status'=>404,
            'data'=>[]
        ];
        return $response;
    }
    public function success($data){
        $response=[
            'message'=>'success',
            'status'=>200,
            'data'=>$data
        ];
        return $response;
    }
    public function created($data){
        $response=[
            'message'=>'created',
            'status'=>201,
            'data'=>$data
        ];
        return $response;
    }
    public function serverError($error){
        $response=[
            'message'=>'failed',
            'error'=>$error,
            'status'=>500,
            'data'=>[]
        ];
        return $response;
    }
    public function delete($tag){
        $response=[
            'message'=>$tag. ' was deleted succesfull',
            'status'=>200,
        ];
        return $response;
    }
}