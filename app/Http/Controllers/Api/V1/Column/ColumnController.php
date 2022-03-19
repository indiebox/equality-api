<?php

namespace App\Http\Controllers\Api\V1\Column;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Column\OrderColumnRequest;
use App\Http\Requests\Api\V1\Column\UpdateColumnRequest;
use App\Http\Resources\V1\Column\ColumnResource;
use App\Models\Column;

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

        return new ColumnResource($column);
    }

    public function order(OrderColumnRequest $request, Column $column)
    {
        $column->moveTo($request->after);

        return new ColumnResource($column);
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

        return response('', 204);
    }
}
