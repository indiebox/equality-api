<?php

namespace App\Http\Resources\V1\User;

use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource implements ResourceWithFields
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
            'id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'
        ]);
    }

    public static function defaultName(): string
    {
        return "users";
    }

    public static function allowedFields(): array
    {
        return [
            'email_verified_at', 'created_at', 'updated_at',
        ];
    }

    public static function defaultFields(): array
    {
        return [
            'id', 'name', 'email',
        ];
    }
}
