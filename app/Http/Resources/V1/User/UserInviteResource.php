<?php

namespace App\Http\Resources\V1\User;

use App\Http\Resources\V1\Team\TeamResource;
use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInviteResource extends JsonResource implements ResourceWithFields
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
            'id', 'created_at', 'updated_at',
        ], [
            'team' => new TeamResource($this->whenLoaded('team')),
            'inviter' => new UserResource($this->whenLoaded('inviter')),
        ]);
    }

    public static function defaultName(): string
    {
        return 'invites';
    }

    public static function defaultFields(): array
    {
        return ['id'];
    }

    public static function allowedFields(): array
    {
        return ['created_at', 'updated_at'];
    }
}
