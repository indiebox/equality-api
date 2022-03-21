<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'id' => $this->visible('id'),
            'name' => $this->visible('name'),
            'email' => $this->visible('email'),
            'email_verified_at' => $this->visible('email_verified_at'),
            'created_at' => $this->visible('created_at'),
            'updated_at' => $this->visible('updated_at'),

            'invites' => UserInviteResource::collection($this->whenLoaded('invites')),
        ];
    }
}
