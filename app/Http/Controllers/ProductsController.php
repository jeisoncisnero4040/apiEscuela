<?php

namespace App\Http\Controllers;

use App\constans\Currencies;
use App\constans\ResponseManager;
use App\Models\ProductModel;
use Cloudinary\Api\Exception\BadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    protected $responseManager;

    public function __construct(ResponseManager $responseManager)
    {
        $this->responseManager=$responseManager; 
    }
    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"products"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Product details",
     *         @OA\JsonContent(
     *             required={"name", "description", "price", "currency"},
     *             @OA\Property(property="name", type="string", example="big data 2"),
     *             @OA\Property(property="description", type="string", example="curso bid data 40 horas con certificado"),
     *             @OA\Property(property="price", type="integer", example=5000),
     *             @OA\Property(property="currency", type="string", example="USD")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="name", type="string", example="big data 2"),
     *                 @OA\Property(property="description", type="string", example="curso bid data 40 horas con certificado"),
     *                 @OA\Property(property="price", type="integer", example=5000),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-13T03:53:55.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-13T03:53:55.000000Z"),
     *                 @OA\Property(property="id", type="integer", example=2)
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
     *         response=500,
     *         description="Error creating product",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating product"),
     *             @OA\Property(property="error", type="string", example="SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'currency' cannot be null"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function CreateProduct(Request $request){
    

        $listCurrencies=Currencies::getCurrencieslist();
        $currenciesString = implode(',', $listCurrencies);
        $currenciesString = strtolower($currenciesString);
        
        $validator=Validator::make($request->all(),[
            'name'=>'required|string',
            'description'=>'required|string',
            'price'=>'required|numeric',
            'currency'=>'nullable|string|in:'.$currenciesString
        ]); 

        if ($validator->fails()){
            $response=$this->responseManager->BadRequest($validator->errors());
            return response()->json($response,400);
        }
        
        $currency=$request->input('currency');
        if (!$currency){
            $currency='mxm';
        }
        try{
            $product=ProductModel::create([
                'name'=>$request->input('name'),
                'description'=>$request->input('description'),
                'price'=>$request->input('price'),
                'currency'=>$currency
            ]);
            $response=$this->responseManager->created($product);
            return response()->json($response,201);

        }catch(\Exception $e)
        {
            $response =$this->responseManager->serverError($e->getMessage());
            return response()->json($response, 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product by ID",
     *     tags={"products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to fetch",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product found",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="big data 2"),
     *                 @OA\Property(property="description", type="string", example="curso bid data 40 horas con certificado"),
     *                 @OA\Property(property="price", type="integer", example=5000),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-13T03:53:55.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-13T03:53:55.000000Z"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function getProductById($id){
        $product=ProductModel::find($id);

        if(!$product){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }

        $response=$this->responseManager->success($product);
        return response()->json($response,200);
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get All product",
     *     tags={"products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to fetch",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product found",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="big data 2"),
     *                 @OA\Property(property="description", type="string", example="curso bid data 40 horas con certificado"),
     *                 @OA\Property(property="price", type="integer", example=5000),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-13T03:53:55.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-13T03:53:55.000000Z"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="failed"),
     *             @OA\Property(property="error", type="string", example="not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    public function getAllProducts(){

        $products=ProductModel::all();
        
        if($products->isEmpty()){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }
        $response=$this->responseManager->success($products);
        return response()->json($response,200);

    }
    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete product by ID",
     *     tags={"products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product deleted successfully"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */

    
    public function deleteProductById($id){
        $product=ProductModel::find($id);

        if(!$product){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }
        $product->delete();

        $response=$this->responseManager->delete('product');
        return response()->json($response,200);
    }

    /**
     * @OA\Post(
     *     path="/api/products/{id}",
     *     summary="Update product by ID",
     *     tags={"products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated product details",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Product Name"),
     *             @OA\Property(property="description", type="string", example="Updated product description"),
     *             @OA\Property(property="price", type="integer", example=100),
     *             @OA\Property(property="currency", type="string", example="usd")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="succes"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="integer"),
     *                 @OA\Property(property="currency", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
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
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating product",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating product"),
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function UpdateProductById($id, Request $request){

        $listCurrencies=Currencies::getCurrencieslist();
        $currenciesString = implode(',', $listCurrencies);
        $currenciesString = strtolower($currenciesString);

        $product=ProductModel::find($id);

        if(!$product){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }



        $validator=Validator::make($request->all(),[
            'name'=>'nullable|string',
            'description'=>'nullable|string',
            'price'=>'nullable|numeric',
            'currency'=>'nullable|string|in:'.$currenciesString
        ]); 

        if ($validator->fails()){
            $response=$this->responseManager->BadRequest($validator->errors());
            return response()->json($response,400);
        }
        
        try{
            $product->update($request->all());
            $response=$this->responseManager->success($product);
            return response()->json($response,200);

        }catch(\Exception $e)
        {
            $response =$this->responseManager->serverError($e->getMessage());
            return response()->json($response, 500);
        }
    }

}
