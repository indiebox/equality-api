<?php

namespace App\Http\Resources\V1\Team;

use App\Http\Resources\V1\User\UserResource;
use App\Services\QueryBuilder\QueryBuilder as Query;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TeamMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return array_merge((new UserResource($this))->toArray($request), [
            'joined_at' => $this->when(Query::hasField('members.joined_at', true), fn() => Carbon::parse($this->pivot->joined_at)),
            'is_creator' => $this->when(Query::hasField('members.is_creator'), fn() => (bool)$this->pivot->is_creator),

            // 'joined_at' => Carbon::parse($this->pivot->joined_at),
            // 'is_creator' => (bool)$this->pivot->is_creator,
        ]);
    }
}
