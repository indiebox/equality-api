<?php

namespace App\Http\Resources\V1\Team;

use App\Http\Resources\V1\User\UserResource;
use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TeamMemberResource extends JsonResource implements ResourceWithFields
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return array_merge((new UserResource($this))->toArray($request), $this->visible([
            'joined_at' => fn() => Carbon::parse($this->pivot->joined_at),
            'is_creator' => fn() => (bool)$this->pivot->is_creator,
        ]));
    }

    public static function defaultName(): string
    {
        return "members";
    }

    public static function defaultFields(): array
    {
        return collect(UserResource::defaultFields())
            ->concat(['joined_at'])
            ->toArray();
    }

    public static function allowedFields(): array
    {
        $base = collect(UserResource::allowedFields())
            ->concat(['is_creator'])
            ->toArray();

        return $base;
    }
}
