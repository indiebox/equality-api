<?php

namespace App\Http\Controllers\Api\V1\Column;

use App\Events\Api\Columns\ColumnDeleted;
use App\Events\Api\Columns\ColumnOrderChanged;
use App\Events\Api\Columns\ColumnUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Column\OrderColumnRequest;
use App\Http\Requests\Api\V1\Column\UpdateColumnRequest;
use App\Http\Resources\V1\Column\ColumnResource;
use App\Models\Column;
use App\Services\QueryBuilder\QueryBuilder;

class ColumnController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Http\Response
     */
    public function show(Column $column)
    {
        $column = QueryBuilder::for($column)
            ->allowedFields([ColumnResource::class], [ColumnResource::class])
            ->get();

        return new ColumnResource($column);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Api\V1\Column\UpdateColumnRequest  $request
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateColumnRequest $request, Column $column)
    {
        $column->update($request->validated());

        broadcast(new ColumnUpdated($column))->toOthers();

        $column = QueryBuilder::for($column)
            ->allowedFields([ColumnResource::class], [ColumnResource::class])
            ->get();

        return new ColumnResource($column);
    }

    public function order(OrderColumnRequest $request, Column $column)
    {
        $column->moveTo($request->after);

        broadcast(new ColumnOrderChanged($column, $request->after))->toOthers();

        return response('', 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Http\Response
     */
    public function destroy(Column $column)
    {
        $column->delete();

        broadcast(new ColumnDeleted($column))->toOthers();

        return response('', 204);
    }
}
