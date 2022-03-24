<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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

    public static function allowedFields($selfName = "users")
    {
        $fields = collect([
            'email_verified_at', 'created_at', 'updated_at',
        ]);

        return $fields->map(fn($value) => $selfName . "." . $value);
    }

    public static function defaultFields($selfName = "users")
    {
        $fields =  collect([
            'id', 'name', 'email',
        ]);

        return $fields->map(fn($value) => $selfName . "." . $value);
    }
}
