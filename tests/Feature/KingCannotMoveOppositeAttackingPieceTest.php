<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KingCannotMoveOppositeAttackingPieceTest extends TestCase
{

    public function test_king_cannot_move_opposite_attacking_piece()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 4, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('rook', 'white', 0, 3),
            $this->newPiece('rook', 'white', 0, 4),
            $this->newPiece('rook', 'white', 0, 5),
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
