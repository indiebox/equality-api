<?php

namespace App\Http\Resources\V1\Team;

use App\Http\Resources\JsonResource;

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
            // 'id' => $this->whenFieldRequested('id'),
            // 'name' => $this->whenFieldRequested('name'),
            // 'description' => $this->whenFieldRequested('description'),
            'url' => $this->whenFieldRequested('url'),
            'logo' => $this->whenFieldRequested('logo', image($this->logo)),
            // 'members' => TeamMemberResource::collection($this->whenLoaded('members')),
            'created_at' => $this->whenFieldRequested('created_at'),
            'updated_at' => $this->whenFieldRequested('updated_at'),

            'members' => TeamMemberResource::collection($this->whenLoaded('members'), 'members'),
            'members_count' => $this->whenFilled('members_count'),
        ];
    }
}
