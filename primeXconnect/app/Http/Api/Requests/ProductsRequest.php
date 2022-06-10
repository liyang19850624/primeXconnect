<?php

namespace App\Http\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductsRequest extends FormRequest
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
            case 'GET':
                return [
                    'stock' => [
                        'boolean'
                    ]
                ];
            case 'POST':
                return [
                    'products' => [
                        'required',
                        'array',
                        'min:1',
                        'max:1000000'
                    ],
                    'products.*.name' => [
                        'required',
                        'string'
                    ],
                    'products.*.description' => [
                        'nullable',
                        'string'
                    ],
                    'products.*.code' => [
                        'required',
                        'string'
                    ]

                ];
            default:
                return [];
        }
    }

    public function messages()
    {
        return [
            'products.required' => 'products is required',
            'products.max' => 'reach maximum limit number of products',
            'products.*.name.required' => 'name is required',
            'products.*.code.required' => 'code is required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['result' => 0, 'errors' => $validator->errors()], 422));
    }
}
