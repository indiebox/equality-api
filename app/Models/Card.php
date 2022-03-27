<?php

namespace App\Models;

use App\Traits\HasOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * App\Models\Column
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property double|null $order
 * @property-read \App\Models\Board $column
 * @property int $column_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team $team
 * @method static \Database\Factories\CardFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Card newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Card newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Card query()
 * @mixin \Eloquent
 */
class Card extends Model
{
    use HasFactory, BelongsToThrough, HasOrder;

    protected $fillable = [
        'name',
        'description',
    ];

    public function getOrderQuery($query)
    {
        return $query->where('column_id', $this->column_id);
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
        return $this->belongsToThrough(Team::class, [Project::class, Board::class, Column::class])
            ->withTrashedParents();
    }
}
