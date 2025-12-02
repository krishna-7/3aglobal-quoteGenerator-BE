<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\UniversalController;
use App\Http\Requests\StorePaymentLinkRequest;
use App\Http\Requests\UpdatePaymentLinkRequest;
use App\Models\PaymentLink;

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
}



