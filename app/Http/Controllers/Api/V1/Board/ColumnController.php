<?php

namespace App\Http\Controllers\Api\V1\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Board\StoreColumnRequest;
use App\Http\Resources\V1\Board\BoardColumnResource;
use App\Models\Board;
use App\Models\Column;

class ColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function index(Board $board)
    {
        return BoardColumnResource::collection($board->columns);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Api\V1\Board\StoreColumnRequest  $request
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function store(StoreColumnRequest $request, Board $board)
    {
        $column = new Column($request->validated());
        $column->board()->associate($board);
        $column->save();

        return (new BoardColumnResource($column))->response()->setStatusCode(201);
    }
}
