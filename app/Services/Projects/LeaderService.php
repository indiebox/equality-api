<?php

namespace App\Services\Projects;

use App\Models\LeaderNomination;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Services\Contracts\Projects\LeaderService as LeaderServiceContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LeaderService implements LeaderServiceContract {
    public function deleteAssociatedNominations(User $user, Team $team)
    {
        $team->projectsLeaderNominations()
            ->where(function (Builder $query) use ($user) {
                $query->where('voter_id', $user->id)
                    ->orWhere('nominated_id', $user->id);
            })
            ->delete();
    }

    public function recalculateProjectLeader(Project $project)
    {
        $nomination = $this->constructGetNominationQuery($project->leaderNominations())->first();

        if ($nomination != null) {
            $project->leader_id = $nomination->nominated_id;
            $project->save();
        } else {
            Project::where('id', $project->id)->update(['leader_id' => $this->getSqlForGetMostOlderMember()]);
        }
    }

    public function recalculateProjectsLeaderInTeam(Team $team)
    {
        // Calculate leader by leader nominations.
        $result = $team->projects()->update([
            'leader_id' => $this->getSqlForGetNominationQuery(),
        ]);

        // Setup most older member of the team as leader of the project
        // if leader from nomination is null.
        $team->projects()
            ->whereNull('leader_id')
            ->update(['leader_id' => $this->getSqlForGetMostOlderMember()]);

        return $result;
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
