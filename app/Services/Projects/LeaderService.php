<?php

namespace App\Services\Projects;

use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\Contracts\Projects\LeaderService as LeaderServiceContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LeaderService implements LeaderServiceContract
{
    public function deleteUserNominations(User $user, Team $team)
    {
        $nominations = $team->projectsLeaderNominations()
            ->where(function (Builder $query) use ($user) {
                $query->where('voter_id', $user->id)
                    ->orWhere('nominated_id', $user->id);
            });

        $projects = $nominations->get(['project_id'])->pluck('project_id')->unique();

        $nominations->delete();

        // Determine the new leader by nominations for affected projects.
        $result = $team->projects()
            ->whereIn('id', $projects)
            ->orWhere('leader_id', $user->id)
            ->update([
                'leader_id' => $this->getSqlForGetNominationQuery(),
            ]);

        // Setup most older member of the team as leader of the projects
        // if leader from nomination is null.
        $team->projects()
            ->whereNull('leader_id')
            ->update(['leader_id' => $this->getSqlForGetMostOlderMember()]);

        return $result;
    }

    public function determineNewLeader(Project $project)
    {
        $nomination = $this->constructGetNominationQuery($project->leaderNominations())->first();

        if ($nomination != null) {
            $project->leader_id = $nomination->nominated_id;
            $project->save();
        } else {
            Project::where('id', $project->id)->update(['leader_id' => $this->getSqlForGetMostOlderMember()]);
        }
    }

    public function makeNominationsCollection(Project $project)
    {
        $members = $project->team->members;

        $membersNominations = $members->map(function ($member) {
            return [
                'nominated_id' => $member->id,
                'nominated' => $member,
                'count' => 0,
                'voters' => [],
            ];
        });

        $nominations = $project->leaderNominations()
            ->get()
            ->groupBy('nominated_id')
            ->map(function ($nomination) use ($members) {
                return [
                    'nominated_id' => $nomination->first()->nominated_id,
                    'nominated' => $members->find($nomination->first()->nominated_id),
                    'count' => $nomination->count(),
                    'voters' => $members->find($nomination->pluck('voter_id')),
                ];
            });

        return $nominations->merge($membersNominations)
            ->unique('nominated_id')
            ->sortByDesc('count')
            ->values();
    }

    protected function getSqlForGetNominationQuery()
    {
        return DB::raw('(' .
            $this->constructGetNominationQuery(LeaderNomination::query())
                ->where('project_id', DB::raw('`projects`.`id`'))
                ->toSql()
        . ')');
    }

    protected function getSqlForGetMostOlderMember()
    {
        return DB::raw('(SELECT user_id FROM `team_user` WHERE team_id = `projects`.`team_id` ORDER BY id asc LIMIT 1)');
    }

    protected function constructGetNominationQuery($query)
    {
        return $query->select('nominated_id')
            ->groupBy('nominated_id')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->orderBy(DB::raw('MAX(updated_at)'))
            ->orderBy(DB::raw('MAX(id)'))
            ->limit(1);
    }
}
