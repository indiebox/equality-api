<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * App\Models\Column
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $order
 * @property-read \App\Models\Board $column
 * @property int $column_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $team
 * @method static \Database\Factories\CardFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Card newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Card newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Card query()
 * @method static \Illuminate\Database\Eloquent\Builder|Card ordered()
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
    | Scopes
    |-------------------------------------------------------------
    */

    public function scopeOrdered(Builder $query)
    {
        return $query->orderByRaw("CASE WHEN `order` is NULL THEN 1 ELSE 0 end, `order`");
    }

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
