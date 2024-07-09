<?php

namespace App\Http\Controllers\API;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentRequest;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $filters = $request->validate([
    //         'sortBy' => ['nullable', 'string', Rule::in(['oldest', 'newest'])],
    //         'limit' => ['nullable', 'integer'],
    //         'page' => ['nullable', 'integer'],
    //         'desc' => ['nullable']
    //     ]);

    //     $limit = $filters['limit'] ?? 10;
    //     $page = $filters['page'] ?? 1;

    //     $payments = Payment::filterAndSort($filters)->paginate($limit, ['*'], 'page', $page);

    //     return PaymentResource::collection($payments);
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StorePaymentRequest $request)
    // {
    //     $payment = Payment::create($request->all());

    //     return response()->json([
    //         'success' => 1,
    //         'data' => [
    //             'uuid' => $payment->uuid
    //         ],
    //         'error' => null,
    //         'errors' => [],
    //         'extra' => []
    //     ], 201);
    // }

    /**
     * Display the specified resource.
     */
    // public function show(string $uuid)
    // {
    //     $payment = $this->getPaymentByUuid($uuid);
    //     if (!$payment) {
    //         return $this->errorResponse("Payment not found", 404);
    //     }

    //     return $this->successResponse(new PaymentResource($payment), 200);
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(UpdatePaymentRequest $request, string $uuid)
    // {
    //     $payment = $this->getPaymentByUuid($uuid);
    //     if (!$payment) {
    //         return $this->errorResponse("Payment not found", 404);
    //     }

    //     $payment->update($request->all());
    //     return $this->successResponse(new PaymentResource($payment->fresh()), 200);
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $uuid)
    // {
    //     $payment = $this->getPaymentByUuid($uuid);
    //     if (!$payment) {
    //         return $this->errorResponse("Payment not found", 404);
    //     }

    //     $payment->delete();
    //     return $this->successResponse([], 200);
    // }

    // private function getPaymentByUuid(string $uuid)
    // {
    //     return Payment::where('uuid', $uuid)->first();
    // }

    // private function successResponse($data, $status = 200)
    // {
    //     return response()->json([
    //         'success' => 1,
    //         'data' => $data,
    //         'error' => null,
    //         'errors' => [],
    //         'extra' => []
    //     ], $status);
    // }

    // private function errorResponse($message, $status = 400)
    // {
    //     return response()->json([
    //         'success' => 0,
    //         'data' => [],
    //         'error' => $message,
    //         'errors' => [],
    //         'extra' => []
    //     ], $status);
    // }
}
