<?php

namespace App\Http\Resources\V1\Project;

use App\Http\Resources\V1\Team\TeamMemberResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LeaderNominationCollection extends ResourceCollection
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $result = collect();

        foreach ($this->collection as $nomination) {
            $result->add([
                'nominated_id' => $nomination['nominated_id'],
                'nominated' => new TeamMemberResource($nomination['nominated']),
                'count' => $nomination['count'],
                'voters' => TeamMemberResource::collection($nomination['voters']),
            ]);
        }

        return [
            'data' => $result,
        ];
    }
}
