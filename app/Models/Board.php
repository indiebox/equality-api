<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Board
 *
 * @property int $id
 * @property-read \App\Models\Project $project
 * @property int $project_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\BoardFactory factory(...$parameters)
 * @method static Builder|Invite newModelQuery()
 * @method static Builder|Invite newQuery()
 * @method static Builder|Invite query()
 * @mixin \Eloquent
 */
class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
