<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BishopMustBlockAttackingPieceTest extends TestCase
{
    public function test_bishop_must_block_attacking_piece()
    {
        $board = $this->newBoard();

        $pieces = [
            $this->newPiece('king', 'black', 0, 4),
            $this->newPiece('king', 'white', 7, 4),
            $this->newPiece('rook', 'white', 1, 0),
            $this->newPiece('bishop', 'white', 3, 7),
            $this->newPiece('bishop', 'black', 6, 2),
            $this->newPiece('rook', 'white', 7, 3),
            $this->newPiece('rook', 'white', 7, 5),
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

        $this->assertEquals([4, 2, 6], $response->json());
    }
}
