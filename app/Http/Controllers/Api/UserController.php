<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\UniversalController;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends UniversalController
{
    /**
     * Set the model class for this controller.
     */
    protected string $modelClass = User::class;

    /**
     * Set the request classes for validation.
     */
    protected ?string $storeRequestClass = StoreUserRequest::class;
    protected ?string $updateRequestClass = UpdateUserRequest::class;

    /**
     * Relationships to eager load.
     */
    protected array $with = ['userType'];

    /**
     * Relationships to count.
     */
    protected array $withCount = [];

    /**
     * Fields to search in.
     */
    protected array $searchFields = ['name', 'email'];

    /**
     * Fields to filter by.
     */
    protected array $filterFields = ['user_type_id'];

    /**
     * Fields to sort by.
     */
    protected array $sortFields = ['id', 'name', 'email', 'created_at', 'updated_at'];

    /**
     * Number of items per page for pagination.
     */
    protected int $perPage = 15;
/**
     * Store a newly created resource in storage.
     * Only User type 1 can create users.
     */
    public function index(Request $request, $modelClass = null): JsonResponse
    {
        try {
            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            // Check if authenticated user has user_type_id = 1
            if (!$authenticatedUser || ($authenticatedUser->user_type_id !== 1||$authenticatedUser->user_type_id!==2)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }
            else{
                return parent::index($request);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     * Only User type 1 can create users.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            // Check if authenticated user has user_type_id = 1
            if (!$authenticatedUser || $authenticatedUser->user_type_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only User Type 1 can create users.',
                ], 403);
            }

            // Call parent store method
            return parent::store($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * User type 2 can edit user information but cannot change user_type_id.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            if (!$authenticatedUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // If authenticated user is type 2, prevent changing user_type_id
            if ($authenticatedUser->user_type_id === 2 && $request->has('user_type_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. User Type 2 cannot change user type.',
                ], 403);
            }

            // Get the user to update
            $user = User::findOrFail($id);

            // Use custom request class if provided
            if ($this->updateRequestClass && class_exists($this->updateRequestClass)) {
                // Create FormRequest instance to get rules
                $formRequest = app($this->updateRequestClass);
                $rules = method_exists($formRequest, 'rules') ? $formRequest->rules() : [];
                
                // Validate using Validator facade
                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation error',
                        'errors' => $validator->errors(),
                    ], 422);
                }
                
                $validated = $validator->validated();
            } else {
                $validated = $request->all();
            }

            // If authenticated user is type 2, remove user_type_id from validated data
            if ($authenticatedUser->user_type_id === 2) {
                unset($validated['user_type_id']);
            }

            // Update the model
            $user->update($validated);

            // Reload with relationships if any
            if (!empty($this->with)) {
                $user->load($this->with);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

