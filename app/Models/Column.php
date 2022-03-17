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
 * @property-read \App\Models\Team $team
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Card[] $cards
 * @property-read int|null $cards_count
 * @method static \Database\Factories\ColumnFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Column newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Column newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Column query()
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

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function team()
    {
        return $this->belongsToThrough(Team::class, [Project::class, Board::class]);
    }
}
