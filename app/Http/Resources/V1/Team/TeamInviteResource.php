<?php

namespace App\Http\Resources\V1\Team;

use App\Http\Resources\V1\User\UserResource;
use App\Services\QueryBuilder\Contracts\ResourceWithFields;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamInviteResource extends JsonResource implements ResourceWithFields
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
            'id', 'accepted_at', 'declined_at', 'created_at', 'updated_at',
        ], [
            'inviter' => new UserResource($this->whenLoaded('inviter')),
            'invited' => new UserResource($this->whenLoaded('invited')),
            'status' => $this->getStatus(),
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
        return ['status', 'accepted_at', 'declined_at', 'created_at', 'updated_at'];
    }
}
