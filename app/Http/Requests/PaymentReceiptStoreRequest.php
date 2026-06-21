<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentReceiptStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isUser();
    }

    public function rules(): array
    {
        $paymentMethod = $this->input('payment_method');

        return [
            'booking_id' => ['nullable', 'integer', Rule::exists('bookings', 'id')->where('user_id', $this->user()?->id)],
            'payment_method' => ['required', Rule::in(['GCash', 'Maya', 'Bank Transfer', 'Cash Payment'])],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'reference_number' => [
                Rule::requiredIf(fn () => in_array($paymentMethod, ['GCash', 'Maya', 'Bank Transfer'], true)),
                'nullable',
                'string',
                'max:100',
            ],
            'transaction_id' => [
                Rule::requiredIf(fn () => $paymentMethod === 'Bank Transfer'),
                'nullable',
                'string',
                'max:100',
            ],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'receipt' => [
                Rule::requiredIf(fn () => $paymentMethod !== 'Cash Payment'),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required' => 'Please attach a receipt file.',
            'receipt.mimes' => 'The receipt must be a JPG, JPEG, PNG, or PDF file.',
            'receipt.max' => 'The receipt must not be larger than 5 MB.',
            'reference_number.required' => 'Please enter the payment reference number.',
            'transaction_id.required' => 'Please enter the bank transaction ID.',
            'payment_method.in' => 'Choose a valid payment method.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
        ];
    }
}
