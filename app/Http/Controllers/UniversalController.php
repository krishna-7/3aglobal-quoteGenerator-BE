<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Universal Controller for CRUD Operations
 * 
 * This controller provides a base implementation for all CRUD operations:
 * - index: List all resources with pagination, search, filtering, and sorting
 * - store: Create a new resource
 * - show: Display a specific resource
 * - update: Update a specific resource
 * - destroy/delete: Delete a specific resource
 * 
 * Usage:
 * 1. Create a controller that extends UniversalController
 * 2. Set the $modelClass property to your model class
 * 3. Optionally set $storeRequestClass and $updateRequestClass for validation
 * 4. Optionally configure $with, $withCount, $searchFields, $filterFields, $sortFields
 * 
 * Example:
 * ```php
 * class PaymentLinkController extends UniversalController
 * {
 *     protected string $modelClass = PaymentLink::class;
 *     protected ?string $storeRequestClass = StorePaymentLinkRequest::class;
 *     protected ?string $updateRequestClass = UpdatePaymentLinkRequest::class;
 *     protected array $with = ['user'];
 *     protected array $searchFields = ['name', 'description'];
 * }
 * ```
 */
abstract class UniversalController extends Controller
{
    /**
     * The model class name for this controller.
     * Child controllers must set this property.
     */
    protected string $modelClass;

    /**
     * The store request class name.
     */
    protected ?string $storeRequestClass = null;

    /**
     * The update request class name.
     */
    protected ?string $updateRequestClass = null;

    /**
     * Get the model instance.
     */
    protected function getModel(): Model
    {
        if (!isset($this->modelClass) || !class_exists($this->modelClass)) {
            throw new \RuntimeException('Model class not set or does not exist in ' . static::class);
        }
        return new $this->modelClass;
    }

    /**
     * Relationships to eager load.
     */
    protected array $with = [];

    /**
     * Relationships to count.
     */
    protected array $withCount = [];

    /**
     * Fields to search in.
     */
    protected array $searchFields = [];

    /**
     * Fields to filter by.
     */
    protected array $filterFields = [];

    /**
     * Fields to sort by.
     */
    protected array $sortFields = [];

    /**
     * Number of items per page for pagination.
     */
    protected int $perPage = 15;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $modelClass = null): JsonResponse
    {
        try {
            $query = $modelClass ?? $this->modelClass::query();

            // Eager load relationships
            if (!empty($this->with)) {
                $query->with($this->with);
            }

            // Load relationship counts
            if (!empty($this->withCount)) {
                $query->withCount($this->withCount);
            }

            // Apply search
            if ($request->has('search') && !empty($this->searchFields)) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    foreach ($this->searchFields as $index => $field) {
                        if ($index === 0) {
                            $q->where($field, 'like', "%{$search}%");
                        } else {
                            $q->orWhere($field, 'like', "%{$search}%");
                        }
                    }
                });
            }

            // Apply filters
            if (!empty($this->filterFields)) {
                foreach ($this->filterFields as $field) {
                    if ($request->has($field)) {
                        $query->where($field, $request->get($field));
                    }
                }
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');

            if (empty($this->sortFields) || in_array($sortBy, $this->sortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', $this->perPage);
            $data = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Use custom request class if provided
            if ($this->storeRequestClass && class_exists($this->storeRequestClass)) {
                // Create FormRequest instance to get rules
                $formRequest = app($this->storeRequestClass);
                $rules = method_exists($formRequest, 'rules') ? $formRequest->rules() : [];

                // Validate using Validator facade
                $validator = Validator::make($request->all(), $rules);
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
            if (Auth::check()) {
                $validated['created_by'] = Auth::id();
            }
            // Create the model instance
            $data = $this->modelClass::create($validated);

            // Reload with relationships if any
            if (!empty($this->with)) {
                $data->load($this->with);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resource created successfully',
                'data' => $data,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating resource',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $query = $this->modelClass::query();

            // Eager load relationships
            if (!empty($this->with)) {
                $query->with($this->with);
            }

            // Load relationship counts
            if (!empty($this->withCount)) {
                $query->withCount($this->withCount);
            }

            $data = $query->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $this->modelClass::findOrFail($id);

            // Use custom request class if provided
            if ($this->updateRequestClass && class_exists($this->updateRequestClass)) {
                // Create FormRequest instance to get rules
                $formRequest = app($this->updateRequestClass);
                $rules = method_exists($formRequest, 'rules') ? $formRequest->rules() : [];

                // Validate using Validator facade
                $validator = Validator::make($request->all(), $rules);
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

            // Update the model
            $data->update($validated);

            // Reload with relationships if any
            if (!empty($this->with)) {
                $data->load($this->with);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resource updated successfully',
                'data' => $data,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating resource',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $this->modelClass::findOrFail($id);
            $data->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resource deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting resource',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (alias for destroy).
     */
    public function delete(int $id): JsonResponse
    {
        return $this->destroy($id);
    }
}

