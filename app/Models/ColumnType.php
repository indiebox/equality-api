<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ColumnType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ColumnType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ColumnType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ColumnType query()
 * @mixin \Eloquent
 */
class ColumnType extends Model
{
    public const NONE = 0;

    public const TODO = 1;

    public const IN_PROGRESS = 2;

    public const DONE = 3;

    public const ON_REVIEW = 4;
}
