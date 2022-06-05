<?php

namespace App\Models;

use App\Traits\Closable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * App\Models\Board
 *
 * @property int $id
 * @property string $name
 * @property-read \App\Models\Project $project
 * @property int $project_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $team
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Column[] $columns
 * @property-read int|null $columns_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Module[] $modules
 * @property-read int|null $modules_count
 * @method static \Database\Factories\BoardFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Board newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Board newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Board query()
 * @mixin \Eloquent
 */
class Board extends Model
{
    use HasFactory, MassPrunable, SoftDeletes, BelongsToThrough, Closable;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subWeek());
    }

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function columns()
    {
        return $this->hasMany(Column::class);
    }

    public function team()
    {
        return $this->belongsToThrough(Team::class, Project::class)
            ->withTrashedParents();
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }
}
