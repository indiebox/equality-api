<?php

namespace App\Http\Resources\V1\Team;

use App\Http\Resources\V1\Project\ProjectResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public static $allowedFields = [
        'id',
        'name',
        'description',
        'url',
        'logo',
        'created_at',
        'updated_at',

        // 'members.name',
        // 'members.joined_at',
        // 'members.is_creator',
    ];

    public static $defaultFields = [
        'id',
        'name',
        'logo',
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
            'description' => $this->visible('description'),
            'url' => $this->visible('url'),
            'logo' => $this->visible('logo', image($this->logo)),
            'created_at' => $this->visible('created_at'),
            'updated_at' => $this->visible('updated_at'),

            'members' => TeamMemberResource::collection($this->whenLoaded('members')),
            'members_count' => $this->visible('members_count'),

            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
        ];
    }
}
