<?php

namespace Tests\Feature\Services\QueryBuilder\Stubs;

use Illuminate\Database\Eloquent\Model;

class QueryableModel extends Model
{
    protected $table = 'models';

    protected $fillable = ['name', 'description', 'timestamp'];

    public function related()
    {
        return $this->hasMany(RelatedModel::class, 'model_id');
    }
}

class RelatedModel extends Model
{
    protected $table = 'related_models';

    protected $fillable = ['name', 'description'];

    public function nested()
    {
        return $this->belongsTo(NestedModel::class, 'nested_id');
    }
}

class NestedModel extends Model
{
    protected $table = 'nested_models';

    protected $fillable = ['name', 'description'];
}
