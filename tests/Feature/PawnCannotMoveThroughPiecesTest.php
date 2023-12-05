<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PawnCannotMoveThroughPiecesTest extends TestCase
{
    public function test_pawn_cannot_move_through_pieces()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 4, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('rook', 'white', 0, 5),
            $this->newPiece('rook', 'white', 3, 0),
            $this->newPiece('rook', 'white', 5, 0),
            $this->newPiece('rook', 'white', 7, 3),
            $this->newPiece('bishop', 'white', 0, 0),
            $this->newPiece('pawn', 'white', 2, 3),
            $this->newPiece('pawn', 'black', 1, 3),
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

        $this->assertEquals([], $response->json());
    }
}
