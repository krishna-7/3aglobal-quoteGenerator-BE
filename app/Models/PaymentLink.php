<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentLink extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_name',
        'payment_mode_id',
        'reference',
        'reference_1',
        'delivery_type',
        'customer_email',
        'customer_phone',
        'email_subject',
        'email_body',
        'email_file_path',
        'sms_body',
        'status',
        'invoice_currency',
        'invoice_amount',
        'tax_type',
        'tax_amount',
        'total_amount',
        'invoice_valid_from',
        'terms_and_conditions',
        'payment_link_url',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_valid_from' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class);
    }
}
