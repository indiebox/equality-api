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
 * @property string $description
 * @property-read \App\Models\Board $column
 * @property int $column_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $team
 * @method static \Database\Factories\CardFactory factory(...$parameters)
 * @method static Builder|Invite newModelQuery()
 * @method static Builder|Invite newQuery()
 * @method static Builder|Invite query()
 * @mixin \Eloquent
 */
class Card extends Model
{
    use HasFactory, BelongsToThrough;

    protected $fillable = [
        'name',
        'description',
    ];

    /*
    |-------------------------------------------------------------
    | Relationships
    |-------------------------------------------------------------
    */

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function team()
    {
        return $this->belongsToThrough(Team::class, [Project::class, Board::class, Column::class]);
    }
}
