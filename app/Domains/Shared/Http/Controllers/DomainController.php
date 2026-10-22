<?php

namespace App\Domains\Shared\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class DomainController extends BaseController
{
    /**
     * Return a successful JSON response with a resource.
     */
    protected function resource(JsonResource $resource): JsonResource
    {
        return $resource;
    }

    /**
     * Return a successful JSON response with a collection.
     */
    protected function collection($resource): JsonResource
    {
        return $resource;
    }

    /**
     * Return a success response with custom data.
     */
    protected function success(array $data = [], int $status = 200, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return an error response.
     */
    protected function error(string $message = 'Error', int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Return a paginated response.
     */
    protected function paginated($resource, Request $request): JsonResponse
    {
        return response()->json($resource->response()->getData(true));
    }

    /**
     * Get the authenticated user.
     */
    protected function user()
    {
        return request()->user();
    }

    /**
     * Check if user has permission.
     */
    protected function authorize(string $ability, mixed $arguments = []): void
    {
        $this->authorize($ability, $arguments);
    }
}
