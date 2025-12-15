<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\UniversalController;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\PaymentMode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ListController extends UniversalController
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
    public function paymentModeIndex(Request $request, $modelClass = null): JsonResponse
    {
        try {
            // Check if authenticated user has user_type_id = 1
            $modelClass = PaymentMode::query();

            // Adjust controller properties for PaymentMode
            $this->searchFields = ['name'];
            $this->sortFields = ['id', 'name', 'created_at', 'updated_at'];
            $this->with = []; // PaymentMode likely doesn't have userType

            return parent::index($request, $modelClass);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

