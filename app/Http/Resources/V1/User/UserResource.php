<?php

namespace App\Http\Resources\V1\User;

use App\Http\Resources\JsonResource;

class UserResource extends JsonResource
{
    public static $defaultFields = [
        'id',
        'name',
        'email',
    ];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->whenFieldRequested('id'),
            'name' => $this->whenFieldRequested('name'),
            'email' => $this->whenFieldRequested('email'),
            'email_verified_at' => $this->whenFieldRequested('email_verified_at'),
            'created_at' => $this->whenFieldRequested('created_at'),
            'updated_at' => $this->whenFieldRequested('updated_at'),
        ];
    }
}
