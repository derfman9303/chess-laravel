<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KingMustCancelCheckTest extends TestCase
{
    public function test_king_must_cancel_check()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 3, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('pawn', 'white', 4, 4),
            $this->newPiece('rook', 'white', 0, 3),
            $this->newPiece('rook', 'white', 0, 4),
            $this->newPiece('rook', 'white', 2, 0),
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

        $this->assertEquals([0, 4, 5], $response->json());
    }
}
