<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KingMustMoveOutOfCheckPawnTest extends TestCase
{
    public function test_example()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 4, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('pawn', 'white', 5, 5),
            $this->newPiece('knight', 'white', 2, 3),
            $this->newPiece('queen', 'black', 2, 0),
        ];

        $this->indexPieces($pieces);
        $this->setKingMoved($pieces);
        $this->loadBoard($board, $pieces);

        $response = $this->post('/get-move', [
            'board'  => $board,
            'pieces' => $pieces,
            'turn'   => false,
            'steps'  => 3,
        ]);

        $this->assertEquals([0, 5, 5], $response->json());
    }
}
