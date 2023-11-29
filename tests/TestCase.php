<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function newBoard() {
        return [
            ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
            ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
            ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
            ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
            ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
            ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
            ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
            ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
        ];
    }

    protected function newPiece($type, $color, $row, $square) {
        return [
            'type'     => $type,
            'color'    => $color,
            'row'      => $row,
            'square'   => $square,
            'moved'    => false,
            'captured' => false,
            'index'    => null,
        ];
    }

    protected function indexPieces(&$pieces): void {
        foreach ($pieces as $index => $piece) {
            $pieces[$index]['index'] = $index;
        }
    }

    /**
     * Iterates over the pieces array and adds their positions to the board array
     */
    protected function loadBoard(&$board, $pieces): void {
        foreach ($pieces as $index => $piece) {
            $board[$piece['row']][$piece['square']] = $index;
        }
    }

    /**
     * This function is used to check the starting position of the kings, and if it's different than the default position, set 'moved' to true.
     * If we don't do this, it causes errors with the castling logic. This is particularly useful for when working with custom board setups.
     */
    protected function setKingMoved(&$pieces) {
        foreach ($pieces as $index => $piece) {
            if ($piece['type'] === 'king') {
                $row = $piece['color'] === 'white' ? 7 : 0;

                if ($piece['row'] != $row || $piece['square'] != 4) {
                    $pieces[$index]['moved'] = true;
                }
            }
        }
    }
}
