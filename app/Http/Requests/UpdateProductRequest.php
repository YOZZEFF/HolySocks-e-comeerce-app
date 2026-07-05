<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product')->id;
        return [

        'slug'=> 'sometimes|string|max:255|unique:products,slug,' . $productId ,
        'category_id'=> 'sometimes|exists:categories,id',
        'is_featured'=> 'sometimes|boolean',
        'is_new_arrival' => 'sometimes|boolean',
        'images'=>'sometimes|array',
        'images.*'=> 'image|mimes:jpg,jpeg,png|max:2048',
        'variants' => 'sometimes|array',
        ];
    }
}
