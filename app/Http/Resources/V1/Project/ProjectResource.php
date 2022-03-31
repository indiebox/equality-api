<?php

namespace App\Http\Resources\V1\Project;

use App\Http\Resources\V1\Team\TeamResource;
use App\Http\Resources\V1\User\UserResource;
use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource implements ResourceWithFields
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
            'id', 'name', 'description', 'image' => image($this->image),
            'deleted_at' => $this->when($this->deleted_at != null, $this->deleted_at),
            'created_at', 'updated_at',
        ], [
            'leader' => new UserResource($this->whenLoaded('leader')),
            'team' => new TeamResource($this->whenLoaded('team')),
        ]);
    }

    public static function defaultName(): string
    {
        return "projects";
    }

    public static function defaultFields(): array
    {
        return ['id', 'name', 'image'];
    }

    public static function allowedFields(): array
    {
        return ['description', 'deleted_at', 'created_at', 'updated_at'];
    }
}
