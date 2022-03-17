<?php

namespace App\Http\Resources\V1\Team;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public static $allowedFilters = [
        'id',
        'name',
        'description',
        'url',
        'logo',
        'created_at',
        'updated_at',

        'members.name',
        'members.joined_at',
        'members.is_creator',
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
            'id' => field($this->id),
            'name' => field($this->name),
            'description' => field($this->description),
            'url' => field($this->url),
            'logo' => field(image($this->logo)),
            'members' => TeamMemberResource::collection($this->whenLoaded('members')),
            'members_count' => field($this->members_count),
            'created_at' => field($this->created_at),
            'updated_at' => field($this->updated_at),
        ];
    }
}
