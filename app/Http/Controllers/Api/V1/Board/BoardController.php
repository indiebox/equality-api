<?php

namespace App\Http\Controllers\Api\V1\Board;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Board\UpdateBoardRequest;
use App\Http\Resources\V1\Board\BoardResource;
use App\Models\Board;

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

        return new BoardResource($board);
    }

    /**
     * Open the specified board.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function open(Board $board)
    {
        $board->open();

        return new BoardResource($board);
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

        return new BoardResource($board);
    }

    /**
     * Restore the specified resource.
     *
     * @param  \App\Models\Board  $board
     * @return \Illuminate\Http\Response
     */
    public function restore(Board $board)
    {
        $board->restore();

        return new BoardResource($board);
    }
}
