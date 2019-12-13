<?php

namespace App\Http\Requests;


class UserAddressRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:255'],
            'zip' => ['required', 'numeric']
        ];
    }

    public function attributes()
    {
        return [
            'zip' => "邮编",
            'contact_name' => '联系人',
            'contact_phone' => '联系电话',
        ];
    }


}
