<?php

namespace App\Http\Resources\V1\Team;

use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource implements ResourceWithFields
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
            'id', 'name', 'description', 'url', 'logo' => image($this->logo), 'created_at', 'updated_at',
        ], [
            'members' => TeamMemberResource::collection($this->whenLoaded('members')),
            'members_count' => $this->when($this->members_count != null, $this->members_count),
        ]);
    }

    public static function defaultName(): string
    {
        return "teams";
    }

    public static function defaultFields(): array
    {
        return [
            'id', 'name', 'logo',
        ];
    }

    public static function allowedFields(): array
    {
        return [
            'description', 'url', 'created_at', 'updated_at',
        ];
    }
}
