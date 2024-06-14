<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\constans\ResponseManager;
use Illuminate\Support\Facades\Validator;
use App\constans\Currencies;
use App\Models\PaymentModel;
use Illuminate\Support\Facades\Crypt;
use PhpParser\Node\Stmt\Return_;

class PaymentsController extends Controller
{
    protected $responseManager;

    public function __construct(ResponseManager $responseManager)
    {
        $this->responseManager=$responseManager; 
    }

    /**
     * @OA\Post(
     *     path="/api/payments",
     *     summary="Register a payment",
     *     tags={"payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Payment details",
     *         @OA\JsonContent(
     *             required={"product_id", "user_id", "value"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="value", type="string", example="5000"),
     *             @OA\Property(property="currency", type="string", example="USD: defaul 'mxn' "),
     *             @OA\Property(property="observations", type="string", example="Payment for service"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment created successfully"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="value", type="string", example="5000"),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="observations", type="string", example="Payment for service"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-13T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-13T12:00:00Z"),
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
     *         description="Error creating payment",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating payment"),
     *             @OA\Property(property="error", type="string", example="Error message"),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function RegisterPayment(Request $request){

        $listCurrencies = Currencies::getCurrencieslist();
        $currenciesString = implode(',', array_map('strtolower', $listCurrencies));

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'value' => 'required|numeric',
            'currency' => 'nullable|in:' . $currenciesString,
            'observations' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            $response = $this->responseManager->BadRequest($validator->errors());
            return response()->json($response, 400);
        }

        $currency = $request->input('currency', 'mxm');
        $value = (string) $request->input('value');

        try {
            $payment = PaymentModel::create([
                'product_id' => $request->input('product_id'),
                'user_id' => $request->input('user_id'),
                'value' => encrypt($value),
                'currency' => encrypt($currency),
                'observations' => $request->input('observations')
            ]);

            $response = $this->responseManager->created($payment);
            return response()->json($response, 201);
        } catch (\Exception $e) {
            $response = $this->responseManager->serverError($e->getMessage());
            return response()->json($response, 500);
        }
    }
        
    /**
     * @OA\Get(
     *     path="/api/payment",
     *     summary="Get all payment",
     *     tags={"payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment retrieved successfully"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="value", type="string", example="5000"),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="observations", type="string", example="Payment for service"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-13T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-13T12:00:00Z"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found"),
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

    public function getAllPayments()
    {
        $payments = PaymentModel::all();

        if ($payments->isEmpty()) {
            $response = $this->responseManager->notFound();
            return response()->json($response, 404);
        }

        foreach ($payments as $payment) {
            try {
                $payment['value'] = decrypt($payment['value']);
                $payment['currency'] = decrypt($payment['currency']);
            } catch (\Exception $e) {
                $response = $this->responseManager->serverError($e->getMessage());
                return response()->json($response, 500);
            }
        }

        $response = $this->responseManager->success($payments);
        return response()->json($response, 200);
    }
    
    /**
     * @OA\Get(
     *     path="/api/payment/{id}",
     *     summary="Get a payment by ID",
     *     tags={"payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment retrieved successfully"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="value", type="string", example="5000"),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="observations", type="string", example="Payment for service"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-13T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-13T12:00:00Z"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found"),
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
    public function getPaymentById($id){
        $payment = PaymentModel::find($id);

        if (!$payment){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }
        
        $payment->value = decrypt($payment->value);
        $payment->currency = decrypt($payment->currency);

        $response = $this->responseManager->success($payment);
        return response()->json($response, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/payment/{id}",
     *     summary="Delete a payment by ID",
     *     tags={"payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment deleted successfully"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found"),
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
    public function deletePaymentById($id){
        $payment = PaymentModel::find($id);

        if (!$payment){
            $response=$this->responseManager->notFound();
            return response()->json($response,404);
        }
        $payment->delete();
        
        $response = $this->responseManager->delete('payment');
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/payment/{id}",
     *     summary="Update a payment by ID",
     *     tags={"payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Payment details to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="value", type="number", example=5000),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="observations", type="string", example="Updated payment for service")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment updated successfully"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="value", type="string", example="5000"),
     *                 @OA\Property(property="currency", type="string", example="USD"),
     *                 @OA\Property(property="observations", type="string", example="Updated payment for service"),
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
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found"),
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
    public function updatePaymentById($id, Request $request){

        $listCurrencies = Currencies::getCurrencieslist();
        $currenciesString = implode(',', array_map('strtolower', $listCurrencies));

        $payment = PaymentModel::find($id);

        if (!$payment) {
            $response = $this->responseManager->notFound();
            return response()->json($response, 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'prohibited|exists:products,id',
            'user_id' => 'prohibited|exists:users,id',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|in:' . $currenciesString,
            'observations' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            $response = $this->responseManager->BadRequest($validator->errors());
            return response()->json($response, 400);
        }

        try {
            $payment->update([
                'value' => $request->input('value') ? encrypt($request->input('value')):$payment->value,
                'currency' => $request->input('currency') ? encrypt($request->input('currency')) : $payment->currency,
                'observations' => $request->input('observations', $payment->observations)
            ]);

            $response = $this->responseManager->success($payment);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = $this->responseManager->serverError($e->getMessage());
            return response()->json($response, 500);
        }
    }

}   
