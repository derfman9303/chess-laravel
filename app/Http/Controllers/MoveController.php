<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MoveService;
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
        event(new \App\Events\TestNotification($request->input('key')));
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
