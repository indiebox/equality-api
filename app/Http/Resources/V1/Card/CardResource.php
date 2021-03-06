<?php

namespace App\Http\Resources\V1\Card;

use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource implements ResourceWithFields
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
            'id', 'name', 'description', 'created_at', 'updated_at',
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
        return ['description', 'created_at', 'updated_at'];
    }
}
