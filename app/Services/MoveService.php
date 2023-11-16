<?php

namespace App\Services;

class MoveService
{
    public function __construct($board, $pieces, $turn, $steps) {
        $this->board     = $board;
        $this->pieces    = $pieces;
        $this->turn      = $turn;
        $this->steps     = $steps;
        $this->kingVal   = 900;
        $this->queenVal  = 90;
        $this->rookVal   = 50; 
        $this->bishopVal = 30;
        $this->knightVal = 30;
        $this->pawnVal   = 10;
    }

    public function getMove($board, $pieces, $turn, $steps) {
        $totalValidPieces = $this->getValidPieces($board, $pieces, false);
        $validPieces      = $totalValidPieces[0];
        $opponentPieces   = $totalValidPieces[1];
        $king             = $totalValidPieces[2];
        $availableMoves   = [];

        if (count($validPieces) > 0) {
            foreach ($validPieces as $validPieceIndex) {
                $piece      = $pieces[$validPieceIndex];
                $oldRow     = $piece['row'];
                $oldSquare  = $piece['square'];
                $validMoves = $this->getValidMoves($board, $piece, $pieces, $oldRow, $oldSquare, $king, $opponentPieces);
                $moveKeys   = array_keys($validMoves);

                foreach ($moveKeys as $moveKey) {
                    $validMove = explode(',', $moveKey);
                    $newRow    = intval($validMove[0]);
                    $newSquare = intval($validMove[1]);

                    if ($validMoves[$moveKey] != 'castle') {
                        // Move piece temporarily
                        $captured = $this->capturePiece($newRow, $newSquare, $board, $pieces, $turn);
                        $moved    = $piece['moved'];
                        $this->movePiece($newRow, $newSquare, $piece, $pieces, $validPieceIndex, $board);

                        // Get value of updated board, save to availableMoves
                        $availableMoves[$oldRow . ',' . $oldSquare . ',' . $newRow . ',' . $newSquare] = $this->maxi($board, $pieces, $steps);

                        // Move piece back to original position, and un-capture the piece if one was captured in the previous temporary move
                        $this->movePiece($oldRow, $oldSquare, $piece, $pieces, $validPieceIndex, $board);
                        $piece['moved'] = $moved;

                        // If a piece was captured, un-capture it
                        $this->unCapturePiece($captured, $newRow, $newSquare, $board, $pieces);
                    } else {
                        $rook          = $pieces[$board[$newRow][$newSquare]];
                        $rookOldRow    = $rook['row'];
                        $rookOldSquare = $rook['square'];

                        // Castle temporarily
                        $this->castle($newRow, $newSquare, $validPieceIndex, $board, $pieces, true);

                        $availableMoves[$oldRow . ',' . $oldSquare . ',' . $newRow . ',' . $newSquare] = $this->maxi($board, $pieces, $steps);

                        // Un-castle
                        $this->unCastle($piece, $rook, $rookOldRow, $rookOldSquare, $oldRow, $oldSquare, $board, $pieces);

                    }
                }
            }
        } else {
            return false;
        }

        $min = min($availableMoves);
        $preferredMoves = [];

        foreach ($availableMoves as $index => $move) {
            if ($move == $min) {
                $preferredMoves[$index] = $move;
            }
        }

        $preferredMoveKeys = array_keys($preferredMoves);

        if (count($preferredMoveKeys) > 0) {
            $randomIndex  = floor(rand(0, count($preferredMoveKeys) - 1));
            $selectedMove = explode(',', $preferredMoveKeys[$randomIndex]);
            
            return [$board[$selectedMove[0]][$selectedMove[1]], $selectedMove[2], $selectedMove[3]];
        } else {
            return false;
        }
    }

    protected function mini($board, $pieces, $steps) {
        if ($steps == 0) {
            return $this->getBoardValue($pieces);
        } else {
            $totalValidPieces = $this->getValidPieces($board, $pieces, false);
            $validPieces      = $totalValidPieces[0];
            $opponentPieces   = $totalValidPieces[1];
            $king             = $totalValidPieces[2]; 
            $min              = INF;

            if (count($validPieces) > 0) {
                foreach ($validPieces as $validPieceIndex) {
                    $piece      = $pieces[$validPieceIndex];
                    $oldRow     = $piece['row'];
                    $oldSquare  = $piece['square'];
                    $validMoves = $this->getValidMoves($board, $piece, $pieces, $oldRow, $oldSquare, $king, $opponentPieces);
                    $moveKeys   = array_keys($validMoves);
    
                    foreach ($moveKeys as $moveKey) {
                        $validMove = explode(',', $moveKey);
                        $newRow    = intval($validMove[0]);
                        $newSquare = intval($validMove[1]);
    
                        if ($validMoves[$moveKey] != 'castle') {

                            // Move piece temporarily
                            $captured = $this->capturePiece($newRow, $newSquare, $board, $pieces, false);
                            $moved    = $piece['moved'];
                            $this->movePiece($newRow, $newSquare, $piece, $pieces, $validPieceIndex, $board);
    
                            // Get value of updated board, save to availableMoves
                            $score = $this->maxi($board, $pieces, $steps - 1);

                            if ($score < $min) {
                                $min = $score;
                            }
    
                            // Move piece back to original position, and un-capture the piece if one was captured in the previous temporary move
                            $this->movePiece($oldRow, $oldSquare, $piece, $pieces, $validPieceIndex, $board);
                            $piece['moved'] = $moved;
        
                            // If a piece was captured, un-capture it
                            $this->unCapturePiece($captured, $newRow, $newSquare, $board, $pieces);
                        } else {
                            $rook          = $pieces[$board[$newRow][$newSquare]];
                            $rookOldRow    = $rook['row'];
                            $rookOldSquare = $rook['square'];
        
                            // Castle temporarily
                            $this->castle($newRow, $newSquare, $validPieceIndex, $board, $pieces, true);
    
                            $score = $this->maxi($board, $pieces, $steps - 1);

                            if ($score < $min) {
                                $min = $score;
                            }
    
                            // Un-castle
                            $this->unCastle($piece, $rook, $rookOldRow, $rookOldSquare, $oldRow, $oldSquare, $board, $pieces);
                        }
                    }
                }

                return $min;
            } else {
                return false;
            }
        }
    }

    protected function maxi($board, $pieces, $steps) {
        if ($steps == 0) {
            return $this->getBoardValue($pieces);
        } else {
            $totalValidPieces = $this->getValidPieces($board, $pieces, true);
            $validPieces      = $totalValidPieces[0];
            $max              = -INF;

            if (count($validPieces) > 0) {
                foreach ($validPieces as $validPieceIndex) {
                    $piece      = $pieces[$validPieceIndex];
                    $oldRow     = $piece['row'];
                    $oldSquare  = $piece['square'];
                    $validMoves = $this->getValidMoves($board, $piece, $pieces, $oldRow, $oldSquare);
                    $moveKeys   = array_keys($validMoves);
    
                    foreach ($moveKeys as $moveKey) {
                        $validMove = explode(',', $moveKey);
                        $newRow    = intval($validMove[0]);
                        $newSquare = intval($validMove[1]);
    
                        if ($validMoves[$moveKey] != 'castle') {
    
                            // Move piece temporarily
                            $captured = $this->capturePiece($newRow, $newSquare, $board, $pieces, true);
                            $moved    = $piece['moved'];
                            $this->movePiece($newRow, $newSquare, $piece, $pieces, $validPieceIndex, $board);
    
                            // Get value of updated board, save to availableMoves
                            $score = $this->mini($board, $pieces, $steps - 1);

                            if ($score > $max) {
                                $max = $score;
                            }
    
                            // Move piece back to original position, and un-capture the piece if one was captured in the previous temporary move
                            $this->movePiece($oldRow, $oldSquare, $piece, $pieces, $validPieceIndex, $board);
                            $piece['moved'] = $moved;
    
                            // If a piece was captured, un-capture it
                            $this->unCapturePiece($captured, $newRow, $newSquare, $board, $pieces);
                        } else {
                            $rook          = $pieces[$board[$newRow][$newSquare]];
                            $rookOldRow    = $rook['row'];
                            $rookOldSquare = $rook['square'];
    
                            // Castle temporarily
                            $this->castle($newRow, $newSquare, $validPieceIndex, $board, $pieces, true);
    
                            $score = $this->mini($board, $pieces, $steps - 1);

                            if ($score > $max) {
                                $max = $score;
                            }
    
                            // Un-castle
                            $this->unCastle($piece, $rook, $rookOldRow, $rookOldSquare, $oldRow, $oldSquare, $board, $pieces);
                        }
                    }
                }
                return $max;
            } else {
                return false;
            }
        }
    }

        /**
     * Adds up the total value of all pieces on the board
     * @param {*} pieces 
     * @returns 
     */
    protected function getBoardValue($pieces) {
        $result = 0;

        foreach ($pieces as $piece) {
            if (!$piece['captured']) {
                switch ($piece['type']) {
                    case "king":
                        $result += $piece['color'] == "white" ? $this->kingVal : -abs($this->kingVal);
                        break;
                    case "queen":
                        $result += $piece['color'] == "white" ? $this->queenVal : -abs($this->queenVal);
                        break;
                    case "rook":
                        $result += $piece['color'] == "white" ? $this->rookVal : -abs($this->rookVal);
                        break;
                    case "bishop":
                        $result += $piece['color'] == "white" ? $this->bishopVal : -abs($this->bishopVal);
                        break;
                    case "knight":
                        $result += $piece['color'] == "white" ? $this->knightVal : -abs($this->knightVal);
                        break;
                    case "pawn":
                        $result += $piece['color'] == "white" ? $this->pawnVal : -abs($this->pawnVal);
                        break;
                    default:
                        break;
                }
            }
        }

        return $result;
    }

    protected function getValidPieces($board, $pieces, $turn = false) {
        $myPieces       = [];
        $opponentPieces = [];
        $myKing         = null;
        $opponentKing   = null;

        foreach ($pieces as $index => $piece) {
            // Find the kings to be used by checkmate logic
            if ($piece['type'] == 'king') {
                if ($piece['color'] == $this->getTurnColor($turn)) {
                    $myKing = $piece;
                } else {
                    $opponentKing = $piece;
                }
            }

            if (!$piece['captured'] && !empty($this->getValidMoves($board, $piece, $pieces, $piece['row'], $piece['square']))) {
                if ($piece['color'] == $this->getTurnColor($turn)) {
                    $myPieces[] = $index;
                } else {
                    $opponentPieces[] = $index;
                }
            }
        }

        return [$myPieces, $opponentPieces, $myKing, $opponentKing];
    }

    protected function getValidMoves($board, $piece, $pieces, $row, $square, $king = false, $opponentPieces = []) {

        switch ($piece['type']) {
            case 'king':
                $result = $this->kingValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king);
                break;
            case 'queen':
                $result = $this->queenValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king);
                break;
            case 'rook':
                $result = $this->rookValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king);
                break;
            case 'bishop':
                $result = $this->bishopValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king);
                break;
            case 'knight':
                $result = $this->knightValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king);
                break;
            case 'pawn':
                $result = $this->pawnValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king);
                break;
        }

        return $result;
    }

    protected function kingValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king = false) {
        $result = [];

        // up
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square), $result);

        // down
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square), $result);

        // left
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row, $square - 1), $result);

        // right
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row, $square + 1), $result);
        
        // up/left diagonal
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square - 1), $result);

        // up/right diagonal
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square + 1), $result);

        // down/left diagonal
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square - 1), $result);

        // down/right diagonal
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square + 1), $result);

        if (!$piece['moved']) {
            // left castle
            if ($this->validLeftCastle($piece, $pieces, $board, $row, $square)) {
                $result[$row . ',' . ($square - 4)] = 'castle';
            }
    
            // right castle
            if ($this->validRightCastle($piece, $pieces, $board, $row, $square)) {
                $result[$row . ',' . ($square + 3)] = 'castle';
            }
        }

        return $result;
    }

    protected function queenValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king = false) {
        $result = [];

        // up
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'up'), $result);

        // down
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'down'), $result);

        // left
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'left'), $result);

        // right
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'right'), $result);

        // up/right diagonal
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'up/right'), $result);

        // up/left diagonal
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'up/left'), $result);

        // down/right diagonal
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'down/right'), $result);

        // down/left diagonal
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'down/left'), $result);

        return $result;
    }

    protected function rookValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king = false) {
        $result = [];

        // up
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'up'), $result);

        // down
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'down'), $result);

        // left
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'left'), $result);

        // right
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'right'), $result);

        return $result;
    }

    protected function bishopValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king = false) {
        $result = [];

        // up/right diagonal
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'up/right'), $result);

        // up/left diagonal
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'up/left'), $result);

        // down/right diagonal
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'down/right'), $result);

        // down/left diagonal
        $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, 'down/left'), $result);

        return $result;
    }

    protected function knightValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king = false) {
        $result = [];

        // up 1
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row - 2, $square - 1), $result);

        // up 2
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row - 2, $square + 1), $result);

        // down 1
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row + 2, $square - 1), $result);

        // down 2
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row + 2, $square + 1), $result);

        // left 1
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square - 2), $result);

        // left 2
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square - 2), $result);

        // right 1
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square + 2), $result);

        // right 2
        $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square + 2), $result);

        return $result;
    }

    protected function pawnValidMoves($board, $piece, $pieces, $row, $square, $opponentPieces, $king = false) {
        $result = [];

        if ($piece['color'] == "white") {
            // forward 1
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square), $result);

            if (!$piece['moved']) {
                // forward 2
                $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row - 2, $square), $result);
            }

            // capture left
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square - 1, true), $result);

            // capture right
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square + 1, true), $result);
        } else {
            // forward 1
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square), $result);

            if (!$piece['moved']) {
                // forward 2
                $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row + 2, $square), $result);
            }

            // capture left
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square - 1, true), $result);

            // capture right
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square + 1, true), $result);
        }

        return $result;
    }

    protected function findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $r, $s) {
        $result = [];

        // Check if r/s coordinates are within the board
        if ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {
            // If king is set, check if the move in question would result in the king being targeted. Otherwise, equate to true so that the checkmate logic is ignored.
            // We can ignore it because if you can make a move on your turn to capture the opponent's $king, you don't need to worry about putting yourself in check or checkmate because the game ends.
            if (!$king ? true : ($this->doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s) == false)) {
                if ($board[$r][$s] == "empty") {
                    $result[$r . ',' . $s] = 'highlighted';
                } elseif ($this->getPiece($board, $pieces, $r, $s)['color'] != $piece['color']) {
                    $result[$r . ',' . $s] = 'capture';
                }
            }
        }

        return $result;
    }

    /**
     * The same as the above function, except it works for pieces that can move in unobstructed lines
     */
    protected function findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $row, $square, $direction) {
        $result = [];
        $r = $row;
        $s = $square;

        // Check if r/s coordinates are still within the board
        while ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {
            if (!$king ? true : ($this->doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s) == false)) {
                if ($board[$r][$s] == "empty") {
                    $result[$r . ',' . $s] = 'highlighted';
                } else if ($this->getPiece($board, $pieces, $r, $s)['color'] != $piece['color']) {
                    $result[$r . ',' . $s] = 'capture';
                    break;
                } else if ($this->getPiece($board, $pieces, $r, $s)['color'] == $piece['color'] && ($r != $row || $s != $square)) {
                    break;
                }
            }
            
            switch ($direction) {
                case 'up':
                    $r--;
                    break;
                case 'down':
                    $r++;
                    break;
                case 'left':
                    $s--;
                    break;
                case 'right':
                    $s++;
                    break;
                case 'up/left':
                    $r--;
                    $s--;
                    break;
                case 'up/right':
                    $r--;
                    $s++;
                    break;
                case 'down/left':
                    $r++;
                    $s--;
                    break;
                case 'down/right':
                    $r++;
                    $s++;
                    break;
            }
        };

        return $result;
    }

    protected function findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $r, $s, $capture = false) {
        $result = [];

        // Check if r/s coordinates are within the board
        if ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {
            if ($capture) {
                if (!$king ? true : ($this->doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s) == false)) {
                    if ($board[$r][$s] != "empty" && $this->getPiece($board, $pieces, $r, $s)['color'] != $piece['color']) {
                        $result[$r . ',' . $s] = 'capture';
                    }
                }
            } else {
                if (!$king ? true : ($this->doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s) == false)) {
                    if ($board[$r][$s] == "empty") {
                        $result[$r . ',' . $s] = 'highlighted';
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns the turn color string, given the boolean turn value. Also accepts the string value, in that case it just returns it.
     * @param {*} turn 
     * @returns 
     */
    protected function getTurnColor($turn) {
        $result = "black";

        if (is_string($turn)) {
            $result = $turn;
        } else {
            if ($turn) {
                $result = "white";
            }
        }

        return $result;
    }

    protected function validLeftCastle($piece, $pieces, $board) {
        $r = $piece['row'];
        $s = $piece['square'];

        if (
            !$piece['moved'] &&
            $board[$r][$s - 1] == "empty" &&
            $board[$r][$s - 2] == "empty" &&
            $board[$r][$s - 3] == "empty" &&
            $board[$r][$s - 4] != "empty" &&
            $this->getPiece($board, $pieces, $r, $s - 4)['color'] == $piece['color'] &&
            $this->getPiece($board, $pieces, $r, $s - 4)['type'] == 'rook' &&
            $this->getPiece($board, $pieces, $r, $s - 4)['moved'] == false
        ) {
            return true;
        }

        return false;
    }

    protected function validRightCastle($piece, $pieces, $board) {
        $r = $piece['row'];
        $s = $piece['square'];

        if (
            !$piece['moved'] &&
            $board[$r][$s + 1] == "empty" &&
            $board[$r][$s + 2] == "empty" &&
            $board[$r][$s + 3] != "empty" &&
            $this->getPiece($board, $pieces, $r, $s + 3)['color'] == $piece['color'] &&
            $this->getPiece($board, $pieces, $r, $s + 3)['type'] == 'rook' &&
            $this->getPiece($board, $pieces, $r, $s + 3)['moved'] == false
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns the piece object, given its location on the board
     * @param {*} board 
     * @param {*} pieces 
     * @param {*} row 
     * @param {*} square 
     * @returns 
     */
    protected function getPiece($board, $pieces, $row, $square) {
        return $pieces[$board[$row][$square]];
    }

    /**
     * Called by the validMove functions for the specific pieces.
     * Moves the piece, then checks that the king isn't targeted in the new board state, and then moves the piece back.
     * @param {*} king 
     * @param {*} opponentPieces 
     * @param {*} r 
     * @param {*} s 
     * @returns 
     */
    protected function doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s) {
        $result    = false;
        $oldRow    = $piece['row'];
        $oldSquare = $piece['square'];

        if ($board[$r][$s] == "empty" || $this->getPiece($board, $pieces, $r, $s)['color'] != $piece['color']) {
            if ($board[$r][$s] != 'castle') {

                // Move piece temporarily
                $captured = $this->capturePiece($r, $s, $board, $pieces, $king['color']);
                $moved = $piece['moved'];
                $this->movePiece($r, $s, $piece, $pieces, $piece['index'], $board);
    
                // Determine if king is targeted in new board state
                $result = $this->kingTargeted($board, $king, $pieces, $opponentPieces);
    
                // Move piece back to original position, and un-capture the piece if one was captured in the previous temporary move
                $this->movePiece($oldRow, $oldSquare, $piece, $pieces, $piece['index'], $board);
                $piece['moved'] = $moved;
    
                // If a piece was captured, un-capture it
                $this->unCapturePiece($captured, $r, $s, $board, $pieces);
            } else {
                // TODO: Simulate castle move. Needs to check if the king passes through check in the process.
            }
        }

        return $result;
    }

    protected function capturePiece($row, $square, &$board, &$pieces, $turn) {
        $result = false;

        // If piece exists on row/square, and belongs to the opposite turn
        if ($board[$row][$square] != 'empty' && $this->getPiece($board, $pieces, $row, $square)['color'] != $this->getTurnColor($turn)) {
            $result = $board[$row][$square];

            $pieces[$result]['captured'] = true;
            $pieces[$result]['row']      = -1;
            $pieces[$result]['square']   = -1;
        }

        return $result;
    }

    protected function unCapturePiece($captured, $row, $square, &$board, &$pieces) {
        if ($captured != false) {
            $piece = $pieces[$captured];
            $moved = $piece['moved'];

            $this->movePiece($row, $square, $piece, $pieces, $captured, $board);

            $pieces[$captured]['moved']    = $moved;
            $pieces[$captured]['captured'] = false;
        }
    }

    protected function movePiece($row, $square, &$piece, &$pieces, $index, &$board) {
        // Check if capture
        if ($board[$row][$square] != 'empty') {
            $capturedPiece           = $this->getPiece($board, $pieces, $row, $square);
            $capturedPiece['captured'] = true;
            $capturedPiece['row']      = -1;
            $capturedPiece['square']   = -1;
        }

        // Move piece on board
        $board[$row][$square] = $index;
        if ($piece['row'] >= 0 && $piece['square'] >= 0) {
            // Only vacate the square if the piece is currently on one
            $board[$piece['row']][$piece['square']] = 'empty';
        }

        // Update piece's coords
        $piece['row']    = $row;
        $piece['square'] = $square;
        $piece['moved']  = true;

        // Update the pieces array
        $pieces[$piece['index']]['row']    = $row;
        $pieces[$piece['index']]['square'] = $square;
        $pieces[$piece['index']]['moved']  = true;
    }

    /**
     * Returns true if the opponent has a piece currently targeting your king (king is in check)
     * 
     * @param {*} king 
     * @param {*} opponentPieces 
     * @param {*} r 
     * @param {*} s 
     * @returns
     */
    protected function kingTargeted($board, $king, $pieces, $opponentPieces) {
        if (count($opponentPieces) > 0) {
            // for (let v = 0; v < opponentPieces.length; v++) {
            foreach ($opponentPieces as $opponentPieceIndex) {
                $opponentPiece = $pieces[$opponentPieceIndex];

                if (!$opponentPiece['captured']) {
                    $validMoves    = $this->getValidMoves($board, $opponentPiece, $pieces, $opponentPiece['row'], $opponentPiece['square']);
                    $validMoveKeys = array_keys($validMoves);
    
                    // for (let m = 0; m < validMoveKeys.length; m++) {
                    foreach ($validMoveKeys as $m) {
                        if ($validMoves[$m] == 'capture') {
                            $splitKey = explode(',', $m);
                            $row      = intval($splitKey[0]);
                            $square   = intval($splitKey[1]);
    
                            if ($row == $king['row'] && $square == $king['square']) {
                                return true;
                            }
                        }
                    } 
                }               
            }
        }

        return false;
    }
}