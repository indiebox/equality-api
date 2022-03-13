<?php

namespace App\Http\Controllers\Api\V1\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Board\StoreColumnRequest;
use App\Http\Resources\V1\Board\BoardColumnResource;
use App\Models\Board;

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
     * @param  \App\Http\Requests\StoreColumnRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreColumnRequest $request)
    {
        //
    }
}
