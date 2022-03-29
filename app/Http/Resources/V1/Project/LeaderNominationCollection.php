<?php

namespace App\Http\Resources\V1\Project;

use App\Http\Resources\V1\Team\TeamMemberResource;
use App\Services\QueryBuilder\QueryBuilder;
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
            $resultPart = [
                'is_leader' => $nomination['is_leader'],
            ];

            if (QueryBuilder::hasInclude('nominated', true)) {
                $resultPart['nominated'] = new TeamMemberResource(QueryBuilder::for($nomination['nominated'])
                    ->allowedFields([
                        TeamMemberResource::class,
                    ], [
                        TeamMemberResource::class,
                    ], 'members')
                    ->unsetRelations('pivot')
                    ->get());
            }

            if (QueryBuilder::hasInclude('voters', true)) {
                $resultPart['voters'] = TeamMemberResource::collection(QueryBuilder::for(collect($nomination['voters']))
                    ->allowedFields([
                        TeamMemberResource::class,
                    ], [
                        TeamMemberResource::class,
                    ], 'members')
                    ->unsetRelations('pivot')
                    ->get());
            }

            if (QueryBuilder::hasInclude('voters_count')) {
                $resultPart['voters_count'] = $nomination['voters_count'];
            }

            $result->add($resultPart);
        }

        return [
            'data' => $result,
        ];
    }
}
