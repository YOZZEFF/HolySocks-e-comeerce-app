<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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

        'name' => 'required|string|max:255',
        'description'=> 'required|string',
        'brand'=> 'sometimes|string|max:255',
        'category_id'=> 'required|exists:categories,id',
        'is_featured'=> 'sometimes|boolean',
        'is_new_arrival' => 'sometimes|boolean',
        'images'=>'sometimes|array',
        'images.*'=> 'image|mimes:jpg,jpeg,png|max:2048',
        'variants' => 'required|array',
        'variants.*.size'   => 'required|in:XS,S,M,L,XL,XXL',
        'variants.*.color'  => 'required|string',
        'variants.*.price'  => 'required|numeric',
        'variants.*.stock'  => 'required|integer',

        ];
    }
}
