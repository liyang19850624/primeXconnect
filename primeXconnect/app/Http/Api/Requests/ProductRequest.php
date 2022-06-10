<?php

namespace App\Http\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
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
                    'id' => [
                        'required',
                        'int',
                        Rule::exists('products')
                    ],
                    'stock' => [
                        'boolean'
                    ]
                ];
            case 'DELETE':
                return [
                    'id' => [
                        'required',
                        'int',
                        Rule::exists('products')
                    ]
                ];
            case 'PATCH':
                return [
                    'id' => [
                        'required',
                        'int',
                        Rule::exists('products')
                    ],
                    'name' => [
                        'required',
                        'string'
                    ],
                    'description' => [
                        'nullable',
                        'string'
                    ],
                    'code' => [
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
            'id.exists' => 'Cannot find product',
            'name.required' => 'name is required',
            'code.required' => 'code is required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['result' => 0, 'errors' => $validator->errors()], 422));
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }
}
