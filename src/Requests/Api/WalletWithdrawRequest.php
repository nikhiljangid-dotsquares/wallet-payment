<?php

namespace admin\wallets\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class WalletWithdrawRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'in:bank,stripe'],
            // method_details is optional now
            'method_details.account_number'      => ['nullable', 'string', 'min:9', 'max:18'],
            'method_details.ifsc'                => ['nullable', 'string', 'size:11'],
            'method_details.account_holder_name' => ['nullable', 'string'],
            'method_details.bank_name'           => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->first();

        throw new HttpResponseException(response()->json([
            'status' => false,
            'code'   => 422,
            'message'=> $error,
            'data'   => (object) [],
            'errors' => $validator->errors(),
        ], 422));
    }
}
