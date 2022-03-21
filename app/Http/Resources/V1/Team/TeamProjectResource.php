<?php

namespace App\Http\Resources\V1\Team;

use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamProjectResource extends JsonResource
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
            'name' => $this->visible('name'),
            'description' => $this->visible('description'),
            'image' => $this->visible('image', image($this->image)),
            // 'leader' => new UserResource($this->leader),
            'leader' => new UserResource($this->whenLoaded('leader')),
            'deleted_at' => $this->when($this->deleted_at != null, $this->deleted_at),
            'created_at' => $this->visible('created_at'),
            'updated_at' => $this->visible('updated_at'),
        ];
    }
}
