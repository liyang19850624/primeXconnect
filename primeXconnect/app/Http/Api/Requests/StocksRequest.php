<?php

namespace App\Http\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StocksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'product_id' => [
                        'required',
                        'int',
                        Rule::exists('products', 'id')
                    ],
                    'stocks' => [
                        'required',
                        'array',
                        'min:1',
                        'max:1000000'
                    ],
                    'stocks.*.on_hand' => [
                        'int',
                        'min:0'
                    ],
                    'stocks.*.taken' => [
                        'int',
                        'min:0'
                    ],
                    'stocks.*.production_date' => [
                        'date',
                        'date_format:Y-m-d H:i:s|before_or_equal:today'
                    ]
                ];
            default:
                return [];
        }
    }

    public function messages()
    {
        return [
            'product_id.required' => 'product_id is required',
            'product_id.exists' => 'Cannot find product',
            'stocks.required' => 'stocks is required',
            'stocks.max' => 'reach the maximum limit number of stocks',
            'stocks.*.on_hand.min' => 'need to have positive number of stock',
            'stocks.*.taken.min' => 'need to have positive number of stock'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['result' => 0, 'errors' => $validator->errors()], 422));
    }
}
