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

        $totalValidPieces = $moveService->getValidPieces($board, $pieces, false);
        $validPieces      = $totalValidPieces[0];
        $opponentPieces   = $totalValidPieces[1];
        $king             = $totalValidPieces[2];
        $availableMoves   = [];

        if (count($validPieces) > 0) {
            foreach ($validPieces as $index => $piece) {

            }
        } else {
            return false;
        }

        return $totalValidPieces;
    }
}
