<?php

namespace App\Http\Resources\V1\Board;

use App\Http\Resources\V1\Project\ProjectResource;
use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardResource extends JsonResource implements ResourceWithFields
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
            'id', 'name', 'created_at', 'updated_at',
            'closed_at' => $this->when($this->isClosed(), $this->closed_at),
            'deleted_at' => $this->when($this->trashed(), $this->deleted_at),
        ], [
            'project' => new ProjectResource($this->whenLoaded('project')),
        ]);
    }

    public static function defaultName(): string
    {
        return "boards";
    }

    public static function defaultFields(): array
    {
        return ['id', 'name', 'closed_at', 'deleted_at'];
    }

    public static function allowedFields(): array
    {
        return ['created_at', 'updated_at'];
    }
}
