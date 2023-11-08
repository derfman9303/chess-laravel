<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MoveController extends Controller
{
    function getMove(Request $request) {
        $data = [
            'board' => $request->input('board'),
            'hello' => 'world',
        ];

        return response()->json($data);
    }
}
