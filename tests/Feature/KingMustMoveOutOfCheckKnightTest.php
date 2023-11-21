<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KingMustMoveOutOfCheckKnightTest extends TestCase
{
    public function test_king_must_move_out_of_check_knight()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 4, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('knight', 'white', 2, 3),
            $this->newPiece('rook', 'white', 3, 0),
            $this->newPiece('rook', 'white', 5, 0),
            $this->newPiece('queen', 'black', 4, 2),
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

        $this->assertEquals([0, 4, 3], $response->json());

    }
}
