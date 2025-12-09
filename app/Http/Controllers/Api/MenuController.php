<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\UniversalController;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class MenuController extends UniversalController
{
    /**
     * Set the model class for this controller.
     */
    protected string $modelClass = Menu::class;

    /**
     * Set the request classes for validation.
     */
    protected ?string $storeRequestClass = StoreMenuRequest::class;
    protected ?string $updateRequestClass = UpdateMenuRequest::class;

    /**
     * Relationships to eager load.
     */
    protected array $with = ['userTypes', 'parent', 'children'];

    /**
     * Relationships to count.
     */
    protected array $withCount = [];

    /**
     * Fields to search in.
     */
    protected array $searchFields = ['name', 'route', 'path'];

    /**
     * Fields to filter by.
     */
    protected array $filterFields = ['parent_id', 'is_active'];

    /**
     * Fields to sort by.
     */
    protected array $sortFields = ['id', 'name', 'order', 'created_at', 'updated_at'];

    /**
     * Number of items per page for pagination.
     */
    protected int $perPage = 15;

    /**
     * Display a listing of the resource.
     * Only admin (user_type_id = 1) can access.
     */
    public function index(Request $request, $modelClass = null): JsonResponse
    {
        try {
            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            // Check if authenticated user has user_type_id = 1 (admin)
            if (!$authenticatedUser || $authenticatedUser->user_type_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Admin can access menus.',
                ], 403);
            }

            // Call parent index method
            return parent::index($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving menus',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * Only admin (user_type_id = 1) can create menus.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            // Check if authenticated user has user_type_id = 1 (admin)
            if (!$authenticatedUser || $authenticatedUser->user_type_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Admin can create menus.',
                ], 403);
            }

            DB::beginTransaction();

            // Validate request
            if ($this->storeRequestClass && class_exists($this->storeRequestClass)) {
                $formRequest = app($this->storeRequestClass);
                $rules = method_exists($formRequest, 'rules') ? $formRequest->rules() : [];
                
                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
                if ($validator->fails()) {
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

            // Extract user_type_ids from validated data
            $userTypeIds = $validated['user_type_ids'] ?? [];
            unset($validated['user_type_ids']);

            // Create the menu
            $menu = Menu::create($validated);

            // Attach user types to menu
            if (!empty($userTypeIds)) {
                $menu->userTypes()->attach($userTypeIds);
            }

            // Reload with relationships
            if (!empty($this->with)) {
                $menu->load($this->with);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Menu created successfully',
                'data' => $menu,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating menu',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * Only admin (user_type_id = 1) can view menus.
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            // Check if authenticated user has user_type_id = 1 (admin)
            if (!$authenticatedUser || $authenticatedUser->user_type_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Admin can view menus.',
                ], 403);
            }

            // Call parent show method
            return parent::show($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving menu',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * Only admin (user_type_id = 1) can update menus.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            // Check if authenticated user has user_type_id = 1 (admin)
            if (!$authenticatedUser || $authenticatedUser->user_type_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Admin can update menus.',
                ], 403);
            }

            DB::beginTransaction();

            // Get the menu to update
            $menu = Menu::findOrFail($id);

            // Validate request
            if ($this->updateRequestClass && class_exists($this->updateRequestClass)) {
                $formRequest = app($this->updateRequestClass);
                $rules = method_exists($formRequest, 'rules') ? $formRequest->rules() : [];
                
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

            // Extract user_type_ids from validated data if present
            $userTypeIds = $validated['user_type_ids'] ?? null;
            unset($validated['user_type_ids']);

            // Update the menu
            $menu->update($validated);

            // Sync user types if provided
            if ($userTypeIds !== null) {
                $menu->userTypes()->sync($userTypeIds);
            }

            // Reload with relationships
            if (!empty($this->with)) {
                $menu->load($this->with);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Menu updated successfully',
                'data' => $menu,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Menu not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating menu',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Only admin (user_type_id = 1) can delete menus.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            // Check if authenticated user has user_type_id = 1 (admin)
            if (!$authenticatedUser || $authenticatedUser->user_type_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Admin can delete menus.',
                ], 403);
            }

            // Call parent destroy method
            return parent::destroy($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting menu',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get menus for the authenticated user based on their user type.
     * This endpoint is accessible to all authenticated users.
     */
    public function getUserMenus(): JsonResponse
    {
        try {
            // Get authenticated user
            $authenticatedUser = JWTAuth::user();
            
            if (!$authenticatedUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Get menus for the user's type
            $menus = Menu::whereHas('userTypes', function ($query) use ($authenticatedUser) {
                $query->where('user_types.id', $authenticatedUser->user_type_id);
            })
            ->where('is_active', true)
            ->where('is_visible', true)
            ->with(['parent', 'children' => function ($query) use ($authenticatedUser) {
                $query->whereHas('userTypes', function ($q) use ($authenticatedUser) {
                    $q->where('user_types.id', $authenticatedUser->user_type_id);
                })->where('is_active', true)->where('is_visible', true)->orderBy('order');
            }])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Menus retrieved successfully',
                'data' => $menus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving menus',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

