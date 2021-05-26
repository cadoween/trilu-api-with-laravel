<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoardListController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Board $board
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Board $board)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid field'], 422);
        }

        $board->load('members');

        if (!$board->members->contains(auth()->user())) {
            return response()->json(['message' => 'unauthorized user'], 401);
        }

        if ($board->boardLists()->count() > 0) {
            $order = $board->boardLists()->max('order') + 1;
        } else {
            $order = 1;
        }

        $board->boardLists()->create(array_merge($validator->validated(), ['order' => $order]));

        return response()->json(['message' => 'create list success']);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\BoardList $boardList
     * @return \Illuminate\Http\Response
     */
    public function show(BoardList $boardList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Board $board
     * @param \App\Models\BoardList $boardList
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Board $board, BoardList $boardList)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'invalid field'], 422);
        }

        $board->load('members');

        if (!$board->members->contains(auth()->user())) {
            return response()->json(['message' => 'unauthorized user'], 401);
        }

        $boardList->update($validator->validated());

        return response()->json(['message' => 'update list success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Board $board
     * @param \App\Models\BoardList $boardList
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destroy(Board $board, BoardList $boardList)
    {
        $board->load('members');

        if (!$board->members->contains(auth()->user())) {
            return response()->json(['message' => 'unauthorized user'], 401);
        }

        $nextBoardLists = $board->boardLists()->where('order', '>', $boardList->order)->get();

        foreach ($nextBoardLists as $nextBoardList) {
            $nextBoardList->update(['order' => $nextBoardList->order - 1]);
        }

        $boardList->delete();

        return response()->json(['message' => 'delete list success']);
    }

    public function moveRight(Board $board, BoardList $boardList)
    {
        $board->load('members');

        if (!$board->members->contains(auth()->user())) {
            return response()->json(['message' => 'unauthorized user'], 401);
        }

        $nextList = $board->boardLists()->where('order', $boardList->order + 1)->first();

        if ($nextList) {
            $boardList->update(['order' => $boardList->order + 1]);

            $nextList->update(['order' => $nextList->order - 1]);
        }

        return response()->json(['message' => 'move success']);
    }

    public function moveLeft(Board $board, BoardList $boardList)
    {
        $board->load('members');

        if (!$board->members->contains(auth()->user())) {
            return response()->json(['message' => 'unauthorized user'], 401);
        }

        $prevList = $board->boardLists()->where('order', $boardList->order - 1)->first();

        if ($prevList) {
            $boardList->update(['order' => $boardList->order - 1]);

            $prevList->update(['order' => $prevList->order + 1]);
        }

        return response()->json(['message' => 'move success']);
    }
}
