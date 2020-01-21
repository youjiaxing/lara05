<?php

namespace App\Http\Requests;

class OrderReviewRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reviews' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // \Log::debug(compact('attribute', 'value'));
                    $ids = array_keys($value);

                    /* @var \Illuminate\Support\Collection $orderItemIds */
                    $orderItemIds = $this->route('order')->orderItems->pluck('id');
                    if ($orderItemIds->diff($ids)->count() == 0 && $orderItemIds->count() == count($ids)) {
                        return;
                    }

                    $fail("未包括订单的所有商品评价");
                    // \Log::debug(var_export($orderItemIds, true));
                }
            ],
            'reviews.*.rating' => ['required', 'integer', 'between:1,5'],
            'reviews.*.content' => ['required', 'string'],
        ];
    }

    public function attributes()
    {
        return [
            'reviews.*.rating' => '评分',
            'reviews.*.content' => '评价内容',
        ];
    }


}
