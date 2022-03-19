<?php

namespace App\Http\Controllers\Api\V1\Card;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Card\MoveCardRequest;
use App\Http\Requests\Api\V1\Card\OrderCardRequest;
use App\Http\Requests\Api\V1\Card\UpdateCardRequest;
use App\Http\Resources\V1\Card\CardResource;
use App\Models\Card;
use App\Models\Column;

class CardController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function show(Card $card)
    {
        return new CardResource($card);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Api\V1\Card\UpdateCardRequest  $request
     * @param  \App\Models\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCardRequest $request, Card $card)
    {
        $card->update($request->validated());

        return new CardResource($card);
    }

    public function order(OrderCardRequest $request, Card $card)
    {
        $card->moveTo($request->after);

        return response('', 204);
    }

    public function move(MoveCardRequest $request, Card $card, Column $column)
    {
        $card->column()->associate($column);
        $card->moveTo($request->after_card);

        return new CardResource($card);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function destroy(Card $card)
    {
        $card->delete();

        return response('', 204);
    }
}
