<?php

namespace App\Http\Requests\Admin\Member;

use App\Enums\ELinkTarget;
use App\Enums\EState;
use App\Http\Requests\Admin\AdminBaseRequest;
use Illuminate\Validation\Rule;

class UpdateMember extends AdminBaseRequest
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
            'avatar' => ['nullable', 'image' , 'mimes:jpeg,png,jpg'],
            'name' => ['required', 'max:191'],
            'surname' => ['required', 'max:191'],
            'user.email' => ['required', 'email', 'max:191'],
            'user.mobile' => ['required', 'max:191'],
            'user.username' => ['required', 'max:191'],
            'user.password' => ['sometimes', 'nullable', 'min:8' , 'confirmed'],
            'user.password_confirmation' => ['sometimes', 'nullable', 'min:8'],
            'roles' => ['required'],
        ];
    }
}
