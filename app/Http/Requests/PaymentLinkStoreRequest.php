<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentLinkStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'payment_mode_id' => 'required|exists:payment_modes,id',
            'reference' => 'required|string|max:255',
            'reference_1' => 'nullable|string|max:255',
            'delivery_type' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:255',
            'email_subject' => 'required|string|max:255',
            'email_body' => 'required|string',
            'email_file_path' => 'nullable|string|max:255',
            'sms_body' => 'nullable|string',
            'status' => 'required|string|max:255',
            'invoice_currency' => 'required|string|max:255',
            'invoice_amount' => 'required|numeric',
            'tax_type' => 'required|string|max:255',
            'tax_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'invoice_valid_from' => 'required|date',
            'terms_and_conditions' => 'nullable|string',
            'payment_link_url' => 'nullable|string|max:255',
            'transaction_type_id' => 'nullable',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
