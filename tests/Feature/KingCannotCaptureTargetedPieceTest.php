<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KingCannotCaptureTargetedPieceTest extends TestCase
{
    public function test_king_cannot_capture_targeted_piece()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 4, 6),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('rook', 'white', 3, 3),
            $this->newPiece('rook', 'white', 3, 5),
            $this->newPiece('pawn', 'white', 4, 7),
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

        $this->assertEquals([0, 4, 7], $response->json());
    }
}
