<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BishopMustCaptureAttackingPieceTest extends TestCase
{
    public function test_bishop_must_capture_attacking_piece()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 4, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('queen', 'white', 0, 5),
            $this->newPiece('rook', 'white', 4, 1),
            $this->newPiece('bishop', 'black', 2, 3),
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

        $this->assertEquals([4, 4, 1], $response->json());
    }
}
