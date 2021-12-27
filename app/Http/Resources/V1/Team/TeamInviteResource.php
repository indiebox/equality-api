<?php

namespace App\Http\Resources\V1\Team;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamInviteResource extends JsonResource
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
            'id' => $this->id,
            'inviter' => $this->inviter,
            'invited' => $this->invited,
            'status' => $this->getStatus(),
            'accepted_at' => $this->accepted_at,
            'declined_at' => $this->declined_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
