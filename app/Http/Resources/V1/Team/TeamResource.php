<?php

namespace App\Http\Resources\V1\Team;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->visible(
            [
                'id', 'name', 'description', 'url', 'logo' => image($this->logo), 'created_at', 'updated_at',
            ],
            [
                'members_count' => $this->when($this->members_count != null, $this->members_count),
                'members' => TeamMemberResource::collection($this->whenLoaded('members')),
            ],
        );
    }

    public static function allowedFields($selfName = "teams")
    {
        $fields = collect([
            'description', 'url', 'created_at', 'updated_at',
        ])
        ->map(fn($value) => $selfName . "." . $value);

        return $fields;
    }

    public static function defaultFields($selfName = "teams")
    {
        $fields =  collect([
            'id', 'name', 'logo',
        ])
        ->map(fn($value) => $selfName . "." . $value);

        return $fields;
    }
}
