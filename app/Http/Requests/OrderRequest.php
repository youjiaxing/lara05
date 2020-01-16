<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderRequest extends Request
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
        return [
            'address_id' => ['required', Rule::exists('user_addresses', 'id')->where('user_id', Auth::id())],
            'remark' => ['nullable', 'max:999'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.amount' => ['required', 'int', 'min:1'],
            'items.*.product_sku_id' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    /* @var ProductSku $productSku */
                    $productSku = ProductSku::query()->find($value);
                    if (is_null($productSku)) {
                        return $fail("商品不存在");
                    }

                    if (!$productSku->product->is_sale) {
                        return $fail("商品已下架");
                    }

                    preg_match('~items\.(\d+)\.product_sku_id~', $attribute, $matches);
                    $index = $matches[1];
                    $amount = (int)$this->input("items.$index.amount");
                    if ($amount <= 0) {
                        return $fail("商品购买数量异常");
                    }

                    if ($productSku->stock < $amount) {
                        return $fail("商品库存不足");
                    }

                }
            ],
        ];
    }

    public function attributes()
    {
        return [
            'items.*.amount' => "商品数量",
        ];
    }

    public function messages()
    {
        return [
            'items.required' => '请选择商品',
        ];
    }
}
