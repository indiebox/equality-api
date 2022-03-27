<?php

namespace App\Http\Resources\V1\Column;

use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ColumnCardResource extends JsonResource implements ResourceWithFields
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->visible([
            'id', 'name', 'created_at', 'updated_at',
        ]);
    }

    public static function defaultName(): string
    {
        return "cards";
    }

    public static function defaultFields(): array
    {
        return ['id', 'name'];
    }

    public static function allowedFields(): array
    {
        return ['created_at', 'updated_at'];
    }
}
