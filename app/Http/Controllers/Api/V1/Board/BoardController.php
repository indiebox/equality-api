<?php

namespace App\Http\Controllers\Api\V1\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Board\OpenBoardRequest;
use App\Http\Requests\Api\V1\Board\RestoreBoardRequest;
use App\Http\Requests\Api\V1\Board\UpdateBoardRequest;
use App\Http\Resources\V1\Board\BoardResource;
use App\Models\Board;
use App\Services\QueryBuilder\QueryBuilder;

class BoardController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function show(Board $board)
    {
        $board = QueryBuilder::for($board)
            ->allowedFields([BoardResource::class], [BoardResource::class])
            ->get();

        return new BoardResource($board);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBoardRequest  $request
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBoardRequest $request, Board $board)
    {
        $board->update($request->validated());

        $board = QueryBuilder::for($board)
            ->allowedFields([BoardResource::class], [BoardResource::class])
            ->get();

        return new BoardResource($board);
    }

    /**
     * Close the specified board.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function close(Board $board)
    {
        $board->close();

        return response('', 204);
    }

    /**
     * Open the specified board.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function open(OpenBoardRequest $request, Board $board)
    {
        $board->open();

        return response('', 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function destroy(Board $board)
    {
        $board->delete();

        return response('', 204);
    }

    /**
     * Restore the specified resource.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function restore(RestoreBoardRequest $request, Board $board)
    {
        $board->restore();

        return response('', 204);
    }
}
