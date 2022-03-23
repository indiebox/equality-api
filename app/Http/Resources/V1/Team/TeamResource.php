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
                'members_count' => $this->when($this->members_count != null, $this->members_count),
            ],
            [
                'members' => TeamMemberResource::collection($this->whenLoaded('members')),
            ],
        );
    }

    public static function allowedFields($relations = [], $selfName = "teams")
    {
        $fields = collect([
            'description', 'url', 'created_at', 'updated_at',
        ])->map(fn($value) => $selfName . "." . $value);

        if (in_array("members", $relations) || array_key_exists("members", $relations)) {
            $fields->push(...collect(TeamMemberResource::allowedFields())->map(fn($value) => "members." . $value));
        }

        return $fields->toArray();
    }

    public static function defaultFields($relations = [])
    {
        $fields =  [
            'id', 'name', 'logo',
        ];

        if (in_array("members", $relations)) {
            array_push($fields, ...TeamMemberResource::defaultFields());
        }

        return $fields;
    }
}
