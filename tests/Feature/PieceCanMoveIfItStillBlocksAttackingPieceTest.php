<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PieceCanMoveIfItStillBlocksAttackingPieceTest extends TestCase
{
    public function test_piece_can_move_if_it_still_blocks_attacking_piece()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 4, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('rook', 'white', 0, 3),
            $this->newPiece('rook', 'white', 0, 5),
            $this->newPiece('queen', 'white', 1, 4),
            $this->newPiece('pawn', 'black', 2, 4),
            $this->newPiece('rook', 'white', 5, 0),
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

        $this->assertEquals([5, 3, 4], $response->json());
    }
}
