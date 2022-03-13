<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * App\Models\Column
 *
 * @property int $id
 * @property string $name
 * @property-read \App\Models\Board $board
 * @property int $board_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Team $team
 * @method static \Database\Factories\ColumnFactory factory(...$parameters)
 * @method static Builder|Invite newModelQuery()
 * @method static Builder|Invite newQuery()
 * @method static Builder|Invite query()
 * @mixin \Eloquent
 */
class Column extends Model
{
    use HasFactory, BelongsToThrough;

    protected $fillable = [
        'name',
    ];

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function project()
    {
        return $this->belongsToThrough(Project::class, Board::class);
    }

    public function team()
    {
        return $this->belongsToThrough(Team::class, [Project::class, Board::class]);
    }
}
