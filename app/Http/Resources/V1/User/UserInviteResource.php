<?php

namespace App\Http\Resources\V1\User;

use App\Http\Resources\V1\Team\TeamResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInviteResource extends JsonResource
{
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
            // 'team' => new TeamResource($this->team),
            // 'inviter' => new UserResource($this->inviter),
            'created_at' => $this->visible('created_at'),
            'updated_at' => $this->visible('updated_at'),
        ];
    }
}
