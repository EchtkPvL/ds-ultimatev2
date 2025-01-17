<?php

namespace App\Http\Requests;

use App\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyBugreportRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('bugreport_delete'), 403, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:bugreports,id',
        ];
    }
}
