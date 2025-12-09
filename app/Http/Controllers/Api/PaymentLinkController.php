<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\UniversalController;
use App\Http\Requests\StorePaymentLinkRequest;
use App\Http\Requests\UpdatePaymentLinkRequest;
use App\Models\PaymentLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentLinkController extends UniversalController
{
    /**
     * Set the model class for this controller.
     */
    protected string $modelClass = PaymentLink::class;

    /**
     * Set the request classes for validation.
     */
    protected ?string $storeRequestClass = StorePaymentLinkRequest::class;
    protected ?string $updateRequestClass = UpdatePaymentLinkRequest::class;

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
    protected array $sortFields = ['id', 'created_at', 'updated_at'];

    /**
     * Number of items per page for pagination.
     */
    protected int $perPage = 15;

    /**
     * Display a listing of the payment links.
     */
    public function index(Request $request, $modelClass = null): JsonResponse
    {
        try {
            $query = PaymentLink::query();

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
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->has('delivery_type')) {
                $query->where('delivery_type', $request->get('delivery_type'));
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, $this->sortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', $this->perPage);
            $data = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Payment links retrieved successfully',
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
                'message' => 'Error retrieving payment links',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created payment link in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Add created_by from authenticated user
        if (Auth::check()) {
            $request->merge(['created_by' => Auth::id()]);
        }

        // Call parent store method
        return parent::store($request);
    }

    /**
     * Update the specified payment link in storage.
     * Override to add updated_by field.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Add updated_by from authenticated user
        if (Auth::check()) {
            $request->merge(['updated_by' => Auth::id()]);
        }

        // Call parent update method
        return parent::update($request, $id);
    }

    /**
     * Display the specified payment link.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $paymentLink = PaymentLink::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Payment link retrieved successfully',
                'data' => $paymentLink,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment link not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment link',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}



