<?php

namespace SwiftApi\Requests;

use Doctrine\DBAL\Types\Type;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SwiftApi\Controllers\DatabaseToolController;
use SwiftApi\Controllers\HelperController;

class PostScaffold extends FormRequest
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
            'table_name' => ['required', 'string', 'max:50'],
            'script' => ['required', 'nullable', 'array', Rule::in(HelperController::$script)],

            'fields.*.name' => ['required', 'max:50'],
            'fields.*.length' => ['integer'],
            'fields.*.type' => ['required', Rule::in(DatabaseToolController::$db_types),],
            'fields.*.key' => ['nullable', Rule::in(DatabaseToolController::$key_types),],
            'fields.*.default' => ['max:50'],
            'fields.*.comment' => ['max:50'],
            'fields.*.nullable' => ['required', 'boolean']
        ];
    }
}
