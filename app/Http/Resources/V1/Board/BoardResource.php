<?php

namespace App\Http\Resources\V1\Board;

use App\Http\Resources\V1\Project\ProjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardResource extends JsonResource
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
            'project' => new ProjectResource($this->project),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->when($this->isClosed(), $this->closed_at),
            'deleted_at' => $this->when($this->trashed(), $this->deleted_at),
        ];
    }
}
