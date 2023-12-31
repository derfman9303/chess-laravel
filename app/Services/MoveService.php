<?php

namespace App\Services;

class MoveService
{
    public function __construct() {
        $this->kingVal   = 900;
        $this->queenVal  = 90;
        $this->rookVal   = 50; 
        $this->bishopVal = 30;
        $this->knightVal = 30;
        $this->pawnVal   = 10;

        $this->whiteKingIndex = null;
        $this->blackKingIndex = null;
    }

    /**
     * The main function of the service. Called once per turn to generate the AI's move to be returned to the front-end.
     * Format of returned data is [<piece index>, <new row>, <new square>]
     */
    public function getMove($board, $pieces, $turn, $steps) {
        $result           = [];
        $availableMoves   = [];
        $totalValidPieces = $this->getValidPieces($board, $pieces, false);
        $validPieces      = $totalValidPieces[0];
        $opponentPieces   = $totalValidPieces[1];
        $king             = $totalValidPieces[2];
        $opponentKing     = $totalValidPieces[3];

        // Defining these as class properties so that the kingTargeted logic doesn't need to iterate over all the pieces to find the kings a second time
        $this->blackKingIndex = $king['index'];
        $this->whiteKingIndex = $opponentKing['index'];

        if (count($validPieces) > 0) {
            $targeted      = $this->getTargetedSquares($board, $pieces, $opponentPieces);
            $targetedBoard = $this->markTargetedSquaresOnBoard($targeted);
            $validMoveData = $this->getValidMoveData($board, $pieces, $opponentPieces, $king, $targetedBoard);

            foreach ($validPieces as $validPieceIndex) {
                $piece      = $pieces[$validPieceIndex];
                $oldRow     = $piece['row'];
                $oldSquare  = $piece['square'];
                $validMoves = $this->calculateValidMoves($board, $piece, $pieces, false, $validMoveData, $king, $opponentPieces);
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
        }

        if (count($availableMoves) > 0) {
            $min = min($availableMoves);
            $preferredMoves = [];
    
            foreach ($availableMoves as $index => $move) {
                if ($move == $min) {
                    $preferredMoves[$index] = $move;
                }
            }

            // If one or more of the preferred moves capture a piece, remove the others that don't
            $this->prioritizeCapturingMoves($board, $pieces, $preferredMoves);
    
            $preferredMoveKeys = array_keys($preferredMoves);
    
            if (count($preferredMoveKeys) > 0) {
                $randomIndex  = floor(rand(0, count($preferredMoveKeys) - 1));
                $selectedMove = array_map('intval', explode(',', $preferredMoveKeys[$randomIndex]));
                
                $result = [$board[$selectedMove[0]][$selectedMove[1]], $selectedMove[2], $selectedMove[3]];
            }
        }

        return $result;
    }

    protected function mini($board, $pieces, $steps) {
        if ($steps == 0) {
            return $this->getBoardValue($pieces);
        } else {
            $totalValidPieces = $this->getValidPieces($board, $pieces, false);
            $validPieces      = $totalValidPieces[0];
            $opponentPieces   = $totalValidPieces[1];
            $king             = $totalValidPieces[2]; 
            $min              = null;

            if (count($validPieces) > 0) {
                $targeted      = $this->getTargetedSquares($board, $pieces, $opponentPieces);
                $targetedBoard = $this->markTargetedSquaresOnBoard($targeted);
                $validMoveData = $this->getValidMoveData($board, $pieces, $opponentPieces, $king, $targetedBoard);

                foreach ($validPieces as $validPieceIndex) {
                    $piece      = $pieces[$validPieceIndex];
                    $oldRow     = $piece['row'];
                    $oldSquare  = $piece['square'];
                    $validMoves = $this->calculateValidMoves($board, $piece, $pieces, false, $validMoveData, $king, $opponentPieces);
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

                            if (is_null($min) || $score < $min) {
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

                            if (is_null($min) || $score < $min) {
                                $min = $score;
                            }
    
                            // Un-castle
                            $this->unCastle($piece, $rook, $rookOldRow, $rookOldSquare, $oldRow, $oldSquare, $board, $pieces);
                        }
                    }
                }

                if (is_null($min)) {
                    // TODO: does this mean checkmate?
                    return $this->getBoardValue($pieces);
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
            // TODO: These are not defined in the original version. Why?
            $opponentPieces   = $totalValidPieces[1];
            $king             = $totalValidPieces[2];
            $max              = null;

            if (count($validPieces) > 0) {
                $targeted      = $this->getTargetedSquares($board, $pieces, $opponentPieces);
                $targetedBoard = $this->markTargetedSquaresOnBoard($targeted);
                $validMoveData = $this->getValidMoveData($board, $pieces, $opponentPieces, $king, $targetedBoard);

                foreach ($validPieces as $validPieceIndex) {
                    $piece      = $pieces[$validPieceIndex];
                    $oldRow     = $piece['row'];
                    $oldSquare  = $piece['square'];
                    $validMoves = $this->calculateValidMoves($board, $piece, $pieces, true, $validMoveData, $king, $opponentPieces);
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

                            if (is_null($max) || $score > $max) {
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

                            if (is_null($max) || $score > $max) {
                                $max = $score;
                            }
    
                            // Un-castle
                            $this->unCastle($piece, $rook, $rookOldRow, $rookOldSquare, $oldRow, $oldSquare, $board, $pieces);
                        }
                    }
                }

                if (is_null($max)) {
                    // TODO: does this mean checkmate?
                    return $this->getBoardValue($pieces);
                }

                return $max;
            } else {
                return false;
            }
        }
    }

    /**
     * Searches the $preferredMoves array for any moves that capture a piece, and if one or more are found, removes the others that don't capture.
     * If the AI can capture a piece, and all else is equal, this function ensures that it does.
     */
    protected function prioritizeCapturingMoves($board, $pieces, &$preferredMoves): void {
        $capturingMoves = [];

        foreach ($preferredMoves as $index => $move) {
            $exploded  = explode(',', $index);
            $oldRow    = $exploded[0];
            $oldSquare = $exploded[1];
            $newRow    = $exploded[2];
            $newSquare = $exploded[3];
            $piece     = $this->getPiece($board, $pieces, $oldRow, $oldSquare);

            // Move captures a piece
            if ($board[$newRow][$newSquare] !== 'empty') {
                $capturingMoves[$index] = $move;
            }
        }

        if (!empty($capturingMoves)) {
            $preferredMoves = $capturingMoves;
        }
    }

    /**
     * Adds up the total value of all pieces on the board to get the score
     * @param {*} pieces 
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

            $validMoves = $this->getValidMoves($board, $piece, $pieces, true);

            // Only unset the targeted moves for the current player, not the opponent. Because the getTargetedSquares logic is using the opponent's valid pieces
            // from this function, and if a piece has no valid moves but is targeting another piece, then that square doesn't get flagged as targeted and
            // therefore the king would be allowed to capture the piece on that square. So we need to include opponent pieces that are also targeting one of
            // their own pieces.
            if ($piece['color'] == $this->getTurnColor($turn)) {
                $this->unsetTargetedMoves($validMoves);
            }

            if (!$piece['captured'] && !empty($validMoves)) {
                if ($piece['color'] == $this->getTurnColor($turn)) {
                    $myPieces[] = $index;
                } else {
                    $opponentPieces[] = $index;
                }
            }
        }

        return [$myPieces, $opponentPieces, $myKing, $opponentKing];
    }

    /**
     * The main function for calculating the valid moves for a given piece, specifically when being called from the mini-max algorithm.
     */
    protected function calculateValidMoves($board, $piece, $pieces, $turn, $validMoveData, $king = false, $opponentPieces = []) {

        $validMoves = $this->getValidMoves($board, $piece, $pieces, false, $king, $opponentPieces, $validMoveData);
        $this->unsetTargetedMoves($validMoves);

        return $validMoves;
    }

    /**
     * Removes the moves with type of 'targeted'. 'Targeted' means any piece that is currently targeted by another piece of its own color.
     * This type of move exists because in the case of determining which moves the king can make without putting itself into check, we need to know
     * which of the opponent's pieces are targeted by themself because the king can't capture those pieces. But in the context of calculateValidMoves(),
     * these moves should be removed because you can't capture your own piece.
     */
    protected function unsetTargetedMoves(&$validMoves): void {
        foreach ($validMoves as $index => $move) {
            if ($move == 'targeted') {
                unset($validMoves[$index]);
            }
        }
    }

    /**
     * Returns four things:
     * 1. The pieces that can only make a single valid move, which would be to capture the piece they're currently protecting the king from. (key is the piece's index, value is the row/square coords)
     * 2. (if king is currently in check) A list of squares that you can move a piece to in order to cancel the check
     * 3. The squares you can move your king to in order to avoid check
     * 4. Whether the king is currently in check (true/false)
     * 
     * This is a lot for one function to return, but I built it this way for efficiency purposes because it can all be done at the same time
     */
    protected function getValidMoveData($board, $pieces, $opponentPieces, $king, $targetedBoard) {
        $result1         = [];
        $result2         = [];
        $result3         = [];
        $result4         = false;
        $attackingPieces = [];

        // TODO: $result3 is showing targeted options for the king's moves that are currently occupied by its own pawns 

        $this->findAttackingPiecesLoop($board, $pieces, 'straight', $king, $attackingPieces, $result1, $result2);
        $this->findAttackingPiecesLoop($board, $pieces, 'diagonal', $king, $attackingPieces, $result1, $result2);
        $this->findAttackingPiecesKnight($board, $pieces, $king, $attackingPieces, $result2);
        $this->findAttackingPiecesPawn($board, $pieces, $king, $attackingPieces, $result2);

        // If there are multiple pieces attacking your king, the only way to cancel the check is to move your king
        if (count($attackingPieces) > 1) {
            $result1 = [];
            $result2 = [];
        }

        // Is the king currently in check?
        if ($targetedBoard[$king['row']][$king['square']] !== "none") {
            $result4 = true;
        }

        // Find the valid moves for the king that aren't targeted
        $result3 = $this->kingValidMoves($board, $king, $pieces, $opponentPieces, false);
        $result3 = $this->removeMovesToTargetedSquares($result3, $targetedBoard);

        return [$result1, $result2, $result3, $result4];
    }

    protected function findAttackingPiecesLoop($board, $pieces, $directionType, $king, &$attackingPieces, &$result1, &$result2): void {
        $straight       = ['up', 'down', 'left', 'right'];
        $diagonal       = ['up/right', 'up/left', 'down/right', 'down/left'];
        $searchForPiece = null;

        if ($directionType == 'straight') {
            $directionArray = $straight;
            $searchForPiece = 'rook';
        } else {
            $directionArray = $diagonal;
            $searchForPiece = 'bishop';
        }


        // Check for rooks or queen attacking the king, and any of our pieces currently blocking an attacking piece
        // (can only move those pieces to capture the blocked attacking piece)
        foreach ($directionArray as $direction) {
            $r = $king['row'];
            $s = $king['square'];

            $blockingPieces = [];
            $highlighted    = [];
            $attackingPiece = null;

            // Update r/s the first time so we don't do the below logic on the king
            $this->updateCoordsByDirection($r, $s, $direction);
    
            // Check if r/s coordinates are still within the board
            while ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {

                if ($board[$r][$s] !== 'empty') {
                    $pieceInQuestion = $this->getPiece($board, $pieces, $r, $s);

                    if ($pieceInQuestion['color'] != $king['color'] && ($pieceInQuestion['type'] == 'queen' || $pieceInQuestion['type'] == $searchForPiece)) {
                        $attackingPiece = $pieceInQuestion['index'];
                        $highlighted[]  = $r . ',' . $s;  

                        // If there are multiple blocking pieces, we don't need to worry about moving only one of them.
                        // Else, add the single blocking piece to $result1
                        if (count($blockingPieces) > 1) {
                            $blockingPieces = [];
                            $highlighted    = [];
                        } elseif (!empty($blockingPieces)) {
                            $result1[$blockingPieces[0]] = $highlighted;
                            $highlighted = [];
                        }

                        break;

                    // Else if the piece is the same color of the king but is not the king, this could potentially be a blocking piece
                    } elseif ($pieceInQuestion['color'] == $king['color'] && $pieceInQuestion['index'] != $king['index']) {
                        $blockingPieces[] = $pieceInQuestion['index'];
                    } else {
                        // If the first opponent piece we find is not one that can attack the king from its position
                        break;
                    }
                } else {
                    $highlighted[] = $r . ',' . $s;
                }

                $this->updateCoordsByDirection($r, $s, $direction);
            }

            // If no attacking piece was found, then we don't need to track which squares you can move to in order to cancel the check
            if (is_null($attackingPiece)) {
                $highlighted = [];
            } else {
                $attackingPieces[] = $attackingPiece;
            }

            $result2 = array_merge($highlighted, $result2);
        }
    }

    protected function findAttackingPiecesPawn($board, $pieces, $king, &$attackingPieces, &$result2): void {
        $row    = $king['row'];
        $square = $king['square'];

        $moves = [
            [$row + 1, $square - 1], // down/left diagonal
            [$row + 1, $square + 1], // down/right diagonal
        ];

        if ($king['color'] == 'white') {
            $moves = [
                [$row - 1, $square - 1], // up/left diagonal
                [$row - 1, $square + 1], // up/right diagonal
            ];
        }

        foreach ($moves as $move) {
            $r = $move[0];
            $s = $move[1];
    
            // Check if r/s coordinates are still within the board
            if ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {

                if ($board[$r][$s] !== 'empty') {
                    $pieceInQuestion = $this->getPiece($board, $pieces, $r, $s);

                    if ($pieceInQuestion['color'] != $king['color'] && $pieceInQuestion['type'] == 'pawn') {
                        $attackingPieces[] = $pieceInQuestion['index'];
                        $result2[] = $r . ',' . $s;
                    }
                }
            }
        }
    }

    protected function findAttackingPiecesKnight($board, $pieces, $king, &$attackingPieces, &$result2): void {
        $row    = $king['row'];
        $square = $king['square'];

        $moves = [
            [$row - 2, $square - 1], // up 1
            [$row - 2, $square + 1], // up 2
            [$row + 2, $square - 1], //down 1
            [$row + 2, $square + 1], // down 2
            [$row + 1, $square - 2], // left 1
            [$row - 1, $square - 2], // left 2
            [$row - 1, $square + 2], // right 1
            [$row + 1, $square + 2], // right 2
        ];

        // Check for knights attacking the king.
        foreach ($moves as $move) {
            $r = $move[0];
            $s = $move[1];
    
            // Check if r/s coordinates are still within the board
            if ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {

                if ($board[$r][$s] !== 'empty') {
                    $pieceInQuestion = $this->getPiece($board, $pieces, $r, $s);

                    if ($pieceInQuestion['color'] != $king['color'] && $pieceInQuestion['type'] == 'knight') {
                        $attackingPieces[] = $pieceInQuestion['index'];
                        $result2[] = $r . ',' . $s;
                    }
                }
            }
        }
    }

    /**
     * Removes the moves in the $validMoves array which would put the piece on a square that the opponent is attacking.
     * This is used to determine where the king can move.
     */
    protected function removeMovesToTargetedSquares($validMoves, $targetedBoard) {
        $result = $validMoves;

        foreach ($validMoves as $index => $move) {
            $exploded = explode(',', $index);
            $row      = $exploded[0];
            $square   = $exploded[1];

            if ($targetedBoard[$row][$square] !== 'none') {
                unset($result[$index]);
            }
        }

        return $result;
    }

    /**
     * Returns a fresh 8x8 array representing the board and all squares currently being targeted by the opponent.
     * We can use this new array to reference the targeted state of a square on the board given specific row/square coords.
     */
    protected function markTargetedSquaresOnBoard($targeted) {
        $result = [
            ['none', 'none', 'none', 'none', 'none', 'none', 'none', 'none'],
            ['none', 'none', 'none', 'none', 'none', 'none', 'none', 'none'],
            ['none', 'none', 'none', 'none', 'none', 'none', 'none', 'none'],
            ['none', 'none', 'none', 'none', 'none', 'none', 'none', 'none'],
            ['none', 'none', 'none', 'none', 'none', 'none', 'none', 'none'],
            ['none', 'none', 'none', 'none', 'none', 'none', 'none', 'none'],
            ['none', 'none', 'none', 'none', 'none', 'none', 'none', 'none'],
            ['none', 'none', 'none', 'none', 'none', 'none', 'none', 'none'],
        ];

        // $index is the row/square coords and $t is the type of targeted square (highlighted, capture)
        foreach ($targeted as $index => $t) {
            $exploded = explode(',', $index);

            $result[$exploded[0]][$exploded[1]] = $t;
        }

        return $result;
    }

    /**
     * Returns the index of the king for the color whose turn it is
     */
    protected function getKingIndex($turn) {
        return $turn ? $this->whiteKingIndex : $this->blackKingIndex;
    }

    /**
     * This function in its current state should only be used to calculate the opponent's targeted squares, because it's not running the getValidMoveData logic.
     * For the player whose turn it is, they can only make a move if it doesn't put their own king into check, which this function doesn't take into account.
     */
    protected function getTargetedSquares($board, $pieces, $opponentPieces) {
        $result = [];

        // $p is the piece index, not the piece object
        foreach ($opponentPieces as $p) {
            if ($pieces[$p]['type'] == 'pawn') {
                // Pawns capture different than they move, so getValidMoves() won't work for them
                $result = array_merge($this->getTargetedSquaresPawn($board, $pieces[$p], $pieces, $opponentPieces), $result);
            } else {
                $result = array_merge($this->getValidMoves($board, $pieces[$p], $pieces), $result);
            }
        }

        return $result;
    }

    /**
     * Returns all the valid moves for a given piece. $valid is used for when checking if the 
     * piece has at least one valid move, in the context of the getValidPieces() function.
     * If set to true, the iteration will stop once a valid move is found, to improve efficiency.
     */
    protected function getValidMoves($board, $piece, $pieces, $valid = false, $king = false, $opponentPieces = [], $validMoveData = [[], [], []]) {

        switch ($piece['type']) {
            case 'king':
                $result = $this->kingValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king);
                break;
            case 'queen':
                $result = $this->queenValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king);
                break;
            case 'rook':
                $result = $this->rookValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king);
                break;
            case 'bishop':
                $result = $this->bishopValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king);
                break;
            case 'knight':
                $result = $this->knightValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king);
                break;
            case 'pawn':
                $result = $this->pawnValidMoves($board, $piece, $pieces, $opponentPieces, $validMoveData, $king);
                break;
        }

        return $result;
    }

    protected function kingValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData = [[], [], []], $king = false) {
        $result = [];
        $row    = $piece['row'];
        $square = $piece['square'];

        $moves = [
            [$row - 1, $square], // up
            [$row + 1, $square], // down
            [$row, $square - 1], // left
            [$row, $square + 1], // right
            [$row - 1, $square - 1], // up/left diagonal
            [$row - 1, $square + 1], // up/right diagonal
            [$row + 1, $square - 1], // down/left diagonal
            [$row + 1, $square + 1], // down/right diagonal
        ];

        foreach ($moves as $move) {
            $row    = $move[0];
            $square = $move[1];

            $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row, $square, $validMoveData), $result);

            // If called from getValidPieces() and a valid move is found, we can stop the iteration because we already know the piece is valid
            if ($valid && !empty($result)) {
                break;
            }
        }

        if (!$piece['moved']) {
            // left castle
            if ($this->validLeftCastle($piece, $pieces, $board, $row, $square)) {
                $result[$piece['row'] . ',' . ($piece['square'] - 4)] = 'castle';
            }
    
            // right castle
            if ($this->validRightCastle($piece, $pieces, $board, $row, $square)) {
                $result[$piece['row'] . ',' . ($piece['square'] + 3)] = 'castle';
            }
        }

        return $result;
    }

    protected function queenValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king = false) {
        $result = [];
        $row    = $piece['row'];
        $square = $piece['square'];

        $moves = [
            'up',
            'down',
            'left',
            'right',
            'up/right',
            'up/left',
            'down/right',
            'down/left',
        ];

        foreach ($moves as $move) {
            $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $move, $valid, $validMoveData), $result);

            // If called from getValidPieces() and a valid move is found, we can stop the iteration because we already know the piece is valid
            if ($valid && !empty($result)) {
                break;
            }
        }

        return $result;
    }

    protected function rookValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king = false) {
        $result = [];
        $row    = $piece['row'];
        $square = $piece['square'];

        $moves = [
            'up',
            'down',
            'left',
            'right',
        ];

        foreach ($moves as $move) {
            $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $move, $valid, $validMoveData), $result);

            // If called from getValidPieces() and a valid move is found, we can stop the iteration because we already know the piece is valid
            if ($valid && !empty($result)) {
                break;
            }
        }

        return $result;
    }

    protected function bishopValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king = false) {
        $result = [];
        $row    = $piece['row'];
        $square = $piece['square'];

        $moves = [
            'up/right',
            'up/left',
            'down/right',
            'down/left',
        ];

        foreach ($moves as $move) {
            $result = array_merge($this->findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $move, $valid, $validMoveData), $result);

            // If called from getValidPieces() and a valid move is found, we can stop the iteration because we already know the piece is valid
            if ($valid && !empty($result)) {
                break;
            }
        }

        return $result;
    }

    protected function knightValidMoves($board, $piece, $pieces, $opponentPieces, $valid, $validMoveData, $king = false) {
        $result = [];
        $row    = $piece['row'];
        $square = $piece['square'];

        $moves = [
            [$row - 2, $square - 1], // up 1
            [$row - 2, $square + 1], // up 2
            [$row + 2, $square - 1], //down 1
            [$row + 2, $square + 1], // down 2
            [$row + 1, $square - 2], // left 1
            [$row - 1, $square - 2], // left 2
            [$row - 1, $square + 2], // right 1
            [$row + 1, $square + 2], // right 2
        ];

        foreach ($moves as $move) {
            $row    = $move[0];
            $square = $move[1];

            $result = array_merge($this->findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $row, $square, $validMoveData), $result);

            // If called from getValidPieces() and a valid move is found, we can stop the iteration because we already know the piece is valid
            if ($valid && !empty($result)) {
                break;
            }
        }

        return $result;
    }

    protected function pawnValidMoves($board, $piece, $pieces, $opponentPieces, $validMoveData, $king = false) {
        $result = [];
        $row    = $piece['row'];
        $square = $piece['square'];

        if ($piece['color'] == "white") {
            // forward 1
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square, $validMoveData), $result);

            // If result is not empty at this point, that means that the pawn's 2-square move is unobstructed
            if (!$piece['moved'] && !empty($result)) {
                // forward 2
                $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row - 2, $square, $validMoveData), $result);
            }

            // capture left
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square - 1, $validMoveData, true), $result);

            // capture right
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row - 1, $square + 1, $validMoveData, true), $result);
        } else {
            // forward 1
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square, $validMoveData), $result);

            // If result is not empty at this point, that means that the pawn's 2-square move is unobstructed
            if (!$piece['moved'] && !empty($result)) {
                // forward 2
                $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row + 2, $square, $validMoveData), $result);
            }

            // capture left
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square - 1, $validMoveData, true), $result);

            // capture right
            $result = array_merge($this->findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $row + 1, $square + 1, $validMoveData, true), $result);
        }

        return $result;
    }

    protected function findValidMoves($board, $king, $piece, $pieces, $opponentPieces, $r, $s, $validMoveData) {
        $result = [];

        // Check if r/s coordinates are within the board
        if ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {
            // If king is set, check if the move in question would result in the king being targeted. Otherwise, equate to true so that the checkmate logic is ignored.
            // We can ignore it because if you can make a move on your turn to capture the opponent's $king, you don't need to worry about putting yourself in check or checkmate because the game ends.
            if (!$king ? true : ($this->doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s, $validMoveData) === false)) {
                if ($board[$r][$s] === "empty") {
                    $result[$r . ',' . $s] = 'highlighted';
                } elseif ($this->getPiece($board, $pieces, $r, $s)['color'] != $piece['color']) {
                    $result[$r . ',' . $s] = 'capture';
                } else {
                    $result[$r . ',' . $s] = 'targeted';
                }
            }
        }

        return $result;
    }

    /**
     * The same as the above function, except it works for pieces that can move in unobstructed lines
     */
    protected function findValidMovesLoop($board, $king, $piece, $pieces, $opponentPieces, $direction, $valid, $validMoveData) {
        $result = [];
        $r      = $piece['row'];
        $s      = $piece['square'];

        $opponentKingFound = false;

        // Check if r/s coordinates are still within the board
        while ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {
            if (!$king ? true : ($this->doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s, $validMoveData) == false)) {
                if ($board[$r][$s] === "empty") {

                    // If the opponent's king is found, consider the next square along the attack path to be targeted so the king knows it can't move there
                    if ($opponentKingFound) {
                        $result[$r . ',' . $s] = 'targeted';
                        break;
                    } else {
                        $result[$r . ',' . $s] = 'highlighted';
                    }

                    // If this function is being called within the context of getValidPieces, we can stop the iteration once a valid move has been found
                    if ($valid) {
                        break;
                    }
                } elseif ($this->getPiece($board, $pieces, $r, $s)['color'] != $piece['color']) {
                    $result[$r . ',' . $s] = 'capture';

                    // If the opponent's king is under attack, we know that the next square over cannot be moved to by that king because it would not cancel the check.
                    // Set $opponentKingFound to true so that we can add one more targeted square to the results.
                    if ($this->getPiece($board, $pieces, $r, $s)['type'] == 'king') {
                        $opponentKingFound = true;
                    } else {
                        break;
                    }

                } elseif ($this->getPiece($board, $pieces, $r, $s)['color'] == $piece['color'] && ($r != $piece['row'] || $s != $piece['square'])) {
                    $result[$r . ',' . $s] = 'targeted';
                    break;
                }
            }
            
            $this->updateCoordsByDirection($r, $s, $direction);
        }

        return $result;
    }

    protected function findValidMovesPawn($board, $king, $piece, $pieces, $opponentPieces, $r, $s, $validMoveData, $capture = false) {
        $result = [];

        // Check if r/s coordinates are within the board
        if ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {
            if ($capture) {
                if (!$king ? true : ($this->doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s, $validMoveData) == false)) {
                    if ($board[$r][$s] !== "empty") {
                        if ($this->getPiece($board, $pieces, $r, $s)['color'] != $piece['color']) {
                            $result[$r . ',' . $s] = 'capture';
                        }
                    } else {
                        $result[$r . ',' . $s] = 'targeted';
                    }
                }
            } else {
                if (!$king ? true : ($this->doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s, $validMoveData) == false)) {
                    if ($board[$r][$s] === "empty") {
                        $result[$r . ',' . $s] = 'highlighted';
                    }
                }
            }
        }

        return $result;
    }

    /**
     * This function will flag the pawn's targeted squares, because the pawnValidMoves() function will only flag the pawn's targeted squares if there is already a piece there.
     * That's problematic when checking which squares the king can move to, because if the square is empty then the king would think that it's a valid square to move to.
     * Separated this logic from pawnValidMoves() to avoid complicating that function any more
     */
    protected function getTargetedSquaresPawn($board, $piece, $pieces, $opponentPieces) {
        $result = [];
        $row    = $piece['row'];
        $square = $piece['square'];

        $moves = [
            [$row + 1, $square - 1], // capture left
            [$row + 1, $square + 1], // capture right
        ];

        if ($piece['color'] == 'white') {
            $moves = [
                [$row - 1, $square - 1], // capture left
                [$row - 1, $square + 1], // capture right
            ];
        }

        foreach ($moves as $move) {
            $r = $move[0];
            $s = $move[1];

            // Check if r/s coordinates are within the board
            if ($r >= 0 && $r < 8 && $s >= 0 && $s < 8) {
                $result[$r . ',' . $s] = 'targeted';
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

    protected function castle($row, $square, $piece, $board, $pieces): void {

        // Identify the rook to be castled
        $rookIndex = $board[$row][$square];

        // Vacate squares
        $board[$pieces[$piece]['row']][$pieces[$piece]['square']] = 'empty';
        $board[$row][$square] = 'empty';

        if ($square == 7) {
            // Move the king
            $this->movePiece($row, $square - 1, $pieces[$piece], $pieces, $piece, $board);

            // Move the rook
            $this->movePiece($row, $square - 2, $pieces[$rookIndex], $pieces, $rookIndex, $board);
        } elseif ($square == 0) {
            // Move the king
            $this->movePiece($row, $square + 2, $pieces[$piece], $pieces, $piece, $board);

            // Move the rook
            $this->movePiece($row, $square + 3, $pieces[$rookIndex], $pieces, $rookIndex, $board);
        }
    }

    protected function unCastle($king, $rook, $rookOldRow, $rookOldSquare, $kingOldRow, $kingOldSquare, &$board, &$pieces): void {
        $this->movePiece($rookOldRow, $rookOldSquare, $rook, $pieces, $rook['index'], $board);
        $this->movePiece($kingOldRow, $kingOldSquare, $king, $pieces, $king['index'], $board);

        $pieces[$rook['index']]['moved'] = false;
        $pieces[$king['index']]['moved'] = false;
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
     *
     * $data1: The pieces that can only make a single valid move, which would be to capture the piece they're currently protecting the king from. (key is the piece's index, value is the row/square coords)
     * $data2: (if king is currently in check) A list of squares that you can move a piece to in order to cancel the check
     * $data3: (if king is currently in check) The squares you can move your king to in order to cancel the check
     * $data4: Whether the king is currently in check (true/false)
     */
    protected function doesMoveCauseCheck($board, $king, $piece, $pieces, $opponentPieces, $r, $s, $validMoveData) {
        $result = false;
        $data1  = $validMoveData[0];
        $data2  = $validMoveData[1];
        $data3  = $validMoveData[2];
        $data4  = $validMoveData[3];

        if ($piece['type'] !== 'king') {
            // If the piece is one that can only make a single valid move, to capture a blocked attacking piece
            if (isset($data1[$piece['index']])) {
                $moves = $data1[$piece['index']];

                if (!in_array(($r . ',' . $s), $moves)) {
                    $result = true;
                }

            // Else if the move we're checking is not one of the moves that would cancel check
            } elseif (!empty($data2) && !in_array(($r . ',' . $s), $data2)) {
                $result = true;
            // Else if the king is in check and the piece has no moves to cancel the check
            } elseif ($data4 && empty($data1) && empty($data2)) {
                $result = true;
            }
        } else {
            // If the piece is the king, and the move we're checking is not one of the found valid moves for the king that avoid check
            if (!in_array(($r . ',' . $s), array_keys($data3))) {
                $result = true;
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

    protected function unCapturePiece($captured, $row, $square, &$board, &$pieces):  void {
        if ($captured != false) {
            $piece = $pieces[$captured];
            $moved = $piece['moved'];

            $this->movePiece($row, $square, $piece, $pieces, $captured, $board);

            $pieces[$captured]['moved']    = $moved;
            $pieces[$captured]['captured'] = false;
        }
    }

    protected function movePiece($row, $square, &$piece, &$pieces, $index, &$board): void {
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

    protected function updateCoordsByDirection(&$r, &$s, $direction): void {
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
    }
}