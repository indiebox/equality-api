<?php

namespace App\Http\Requests\Api\V1\Column;

use App\Rules\Api\ColumnInSameBoard;
use Illuminate\Foundation\Http\FormRequest;

class OrderColumnRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'after' => ['present', 'integer', 'min:0', new ColumnInSameBoard($this, $this->route('column')->board)],
        ];
    }
}
