<?php

namespace App\Http\Resources\V1\Project;

use App\Http\Resources\V1\Team\TeamResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'image' => $this->image,
            'team' => new TeamResource($this->team),
            'leader' => new UserResource($this->leader),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
