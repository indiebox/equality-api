<?php

namespace App\Http\Resources\V1\Team;

use App\Http\Resources\JsonResource;
use App\Http\Resources\V1\User\UserResource;

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
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => image($this->image),
            // 'leader' => new UserResource($this->leader),
            'leader' => new UserResource($this->whenLoaded('leader'), 'leader'),
            'deleted_at' => $this->when($this->deleted_at != null, $this->deleted_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
