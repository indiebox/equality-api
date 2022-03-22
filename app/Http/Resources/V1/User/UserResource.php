<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static function allowedFields($relations = [])
    {
        $fields = [
            'email_verified_at', 'created_at', 'updated_at',
        ];

        return $fields;
    }

    public static function defaultFields()
    {
        return [
            'id', 'name', 'email',
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->visible(
            ['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at'],
        );
    }
}
