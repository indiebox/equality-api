<?php

namespace App\Http\Controllers\Api\V1\Column;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Column\StoreCardRequest;
use App\Http\Resources\V1\Card\CardResource;
use App\Http\Resources\V1\Column\ColumnCardResource;
use App\Models\Card;
use App\Models\Column;
use App\Services\QueryBuilder\QueryBuilder;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Http\Response
     */
    public function index(Column $column)
    {
        $cards = QueryBuilder::for($column->cards()->orderByPosition())
            ->allowedFields([ColumnCardResource::class], [ColumnCardResource::class])
            ->get();

        return ColumnCardResource::collection($cards);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Api\V1\Column\StoreCardRequest  $request
     * @param  \App\Models\Column  $column
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCardRequest $request, Column $column)
    {
        $card = new Card($request->validated());
        $card->column()->associate($column);
        $card->moveTo($request->after_card);

        return (new CardResource($card))->response()->setStatusCode(201);
    }
}
