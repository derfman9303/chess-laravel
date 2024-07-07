<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MoveService;
use App\Events\StartGame;
use App\Events\PlayerMoved;
use Pusher\Pusher;

class MoveController extends Controller
{
    function getMove(Request $request) {
        $board  = $request->input('board');
        $pieces = $request->input('pieces');
        $turn   = $request->input('turn');
        $steps  = $request->input('steps');

        $moveService = new MoveService;

        return $moveService->getMove($board, $pieces, $turn, $steps);
    }

    function makeMove(Request $request) {
        $key    = $request->input('key');
        $board  = $request->input('board');
        $pieces = $request->input('pieces');
        $turn   = $request->input('turn');

        $response = [
            'key'    => $key,
            'board'  => $board,
            'pieces' => $pieces,
            'turn'   => $turn,
        ];

        event(new PlayerMoved($key, $board, $pieces, $turn));

        return response()->json($response);
    }

    function startGame(Request $request) {
        $playerOneIsWhite = (bool)random_int(0, 1);
        $timeLimit = $request->input('timeLimit', 30);

        $response = [
            'playerOneIsWhite' => $playerOneIsWhite,
            'timeLimit' => $timeLimit,
        ];

        event(new StartGame($request->input('key'), $playerOneIsWhite, $timeLimit));

        return response()->json($response);
    }

    function isChannelOccupied(Request $request) {
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'), 
            config('broadcasting.connections.pusher.secret'), 
            config('broadcasting.connections.pusher.app_id'),
            ['cluster' => 'us2'],
        );

        // Get channel name from the request
        $channelName = $request->input('channel');

        // Fetch channel information
        $channelInfo = $pusher->get('/channels/' . $channelName);

        $occupied = $channelInfo->occupied;

        return response()->json(['occupied' => $occupied]);
    }
}
