<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a success response.
     *
     * @param  mixed  $data
     */
    protected function success($data = [], string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Send an error response.
     *
     * @param  mixed  $data
     */
    protected function error(string $message = 'Error', int $code = 400, $data = []): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Send a paginated response.
     *
     * @param  mixed  $resourceCollection
     */
    protected function paginated($resourceCollection, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = $resourceCollection->response()->getData(true);

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $response['data'],
            'meta' => $response['meta'] ?? [],
            'links' => $response['links'] ?? [],
        ], $code);
    }

    /**
     * Send a simplified paginated response.
     *
     * @param  mixed  $resourceCollection
     */
    protected function paginatedSimplified($resourceCollection, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = $resourceCollection->response()->getData(true);

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $response['data'],
            'pagination' => [
                'total' => $response['meta']['total'] ?? 0,
                'per_page' => $response['meta']['per_page'] ?? 0,
                'prev' => $response['links']['prev'] ?? null,
                'next' => $response['links']['next'] ?? null,
            ],
        ], $code);
    }
}
