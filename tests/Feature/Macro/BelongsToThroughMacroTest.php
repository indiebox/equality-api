<?php

namespace Tests\Feature\Macro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Znck\Eloquent\Traits\BelongsToThrough;

class BelongsToThroughMacroTest extends TestCase
{
    public function test_with_trashed_parents_macro()
    {
        $model = new EloquentModel();

        $expected = "select * from `eloquent_model5s` inner join `eloquent_model4s`"
        . " on `eloquent_model4s`.`eloquent_model5_id` = `eloquent_model5s`.`id`"
        . " inner join `eloquent_model3s` on `eloquent_model3s`.`eloquent_model4_id` = `eloquent_model4s`.`id`"
        . " inner join `eloquent_model2s` on `eloquent_model2s`.`eloquent_model3_id` = `eloquent_model3s`.`id`"
        . " where `eloquent_model2s`.`id` is null and `eloquent_model4s`.`deleted_at` is null"
        . " and `eloquent_model2s`.`deleted_at` is null";

        $this->assertEquals($expected, $model->relation()->toSql());

        $expected = "select * from `eloquent_model5s` inner join `eloquent_model4s`"
        . " on `eloquent_model4s`.`eloquent_model5_id` = `eloquent_model5s`.`id`"
        . " inner join `eloquent_model3s` on `eloquent_model3s`.`eloquent_model4_id` = `eloquent_model4s`.`id`"
        . " inner join `eloquent_model2s` on `eloquent_model2s`.`eloquent_model3_id` = `eloquent_model3s`.`id`"
        . " where `eloquent_model2s`.`id` is null";

        $this->assertEquals($expected, $model->relationWithTrashed()->toSql());
    }
}

class EloquentModel extends Model
{
    use BelongsToThrough;

    public function relation()
    {
        return $this->belongsToThrough(
            EloquentModel5::class,
            [EloquentModel4::class, EloquentModel3::class, EloquentModel2::class]
        );
    }

    public function relationWithTrashed()
    {
        return $this->relation()
            ->withTrashedParents();
    }
}

class EloquentModel2 extends Model
{
    use SoftDeletes;
}

class EloquentModel3 extends Model
{
}

class EloquentModel4 extends Model
{
    use SoftDeletes;
}

class EloquentModel5 extends Model
{
}
