<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KingCannotCaptureTargetedPiece2Test extends TestCase
{
    /**
     * Similar to the first one, except this one is testing against pieces that are targeted by another piece that doesn't have any valid moves.
     */
    public function test_king_cannot_capture_targeted_piece_2()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 4, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('rook', 'white', 4, 2),
            $this->newPiece('pawn', 'white', 4, 3),
            $this->newPiece('pawn', 'white', 4, 1),
            $this->newPiece('pawn', 'white', 3, 2),
            $this->newPiece('pawn', 'white', 5, 2),
            $this->newPiece('rook', 'white', 3, 7),
            $this->newPiece('rook', 'white', 5, 7),
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
