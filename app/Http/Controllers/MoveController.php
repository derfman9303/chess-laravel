<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MoveService;

class MoveController extends Controller
{
    function getMove(Request $request) {
        $board  = $request->input('board');
        $pieces = $request->input('pieces');
        $turn   = $request->input('turn');
        $steps  = $request->input('steps');

        $moveService = new MoveService($board, $pieces, $turn, $steps);

        return $moveService->getMove($board, $pieces, $turn, $steps);
    }
}
