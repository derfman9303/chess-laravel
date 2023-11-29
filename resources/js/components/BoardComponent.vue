<template>
    <div
        id="board"
        class="position-absolute top-50 start-50 translate-middle"
        @click="handleClick"
    >
        <div class="d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
    </div>
</template>

<script>
    import chessMixin from '../mixins/chessMixin';

    export default {
        name: 'BoardComponent',
        mixins: [chessMixin],
        data() {
            return {
               grid: [],
               squares: [],
               board: [
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                ],
                pieces: [],
                selectedPiece: null,
                turn: true,

                // blackCaptured: null,
                // whiteCaptured: null,
            }
        },

        methods: {
            async handleClick(event) {
                // For tracking execution time
                const t0 = performance.now();

                // Starting from the clicked element
                let currentElement = event.target;

                // Continue traversing up the DOM tree until a parent with the 'square' class is found
                while (currentElement && !currentElement.classList.contains('square')) {
                    currentElement = currentElement.parentElement;
                }

                // If the user clicked on a square (or the svg/path of the piece inside the square), otherwise do nothing
                if (currentElement && currentElement.classList.contains('square')) {

                    let r = parseInt(currentElement.getAttribute('data-r'));
                    let s = parseInt(currentElement.getAttribute('data-s'));

                    if (this.selectedPiece !== null) {
                        let piece = this.pieces[this.selectedPiece];

                        if (this.grid[r][s].classList.contains("highlighted") || this.grid[r][s].classList.contains("capture")) {

                            // Add highlighting before moving/castling the piece because otherwise the piece's row/square coords will be updated to match r/s
                            this.removeHighlighting();
                            this.removePreviousMoveHighlighting();
                            this.addPreviousMoveHighlighting(piece.row, piece.square, r, s, this.grid);
                            this.movePiece(r, s);
                            this.switchTurns();
                            this.reloadGrid();
                        } else if (this.grid[r][s].classList.contains("castle")) {

                            this.removeHighlighting();
                            this.removePreviousMoveHighlighting();
                            this.addPreviousMoveHighlighting(piece.row, piece.square, r, s, this.grid);
                            this.castle(r, s, this.selectedPiece, this.board, this.pieces);
                            this.switchTurns();
                            this.reloadGrid();
                        } else {
                            // The player clicked off the selected piece, so the highlighting should be cleared
                            this.removeHighlighting();
                        }

                        this.selectedPiece = null;

                        // AI makes move
                        if (!this.turn) {
                            const move      = await this.getMove(this.board, this.pieces, this.turn, 3);
                            const index     = parseInt(move[0]);
                            const row       = parseInt(move[1]);
                            const square    = parseInt(move[2]);
                            let piece       = this.pieces[index];
                            const oldRow    = piece.row;
                            const oldSquare = piece.square;

                            console.log(move);

                            if (move !== false) {
                                if (this.validCastle(this.pieces[this.board[0][4]], this.pieces, this.board, row, square)) {
                                    this.castle(row, square, this.board[0][4], this.board, this.pieces);
                                } else {
                                    this.movePiece(row, square, piece, this.pieces, index, this.board);
                                }

                                this.removeHighlighting();
                                this.removePreviousMoveHighlighting();
                                this.addPreviousMoveHighlighting(oldRow, oldSquare, piece.row, piece.square, this.grid);
                                this.switchTurns();
                                this.reloadGrid();
                            } else {
                                // Checkmate by white? Stalemate?
                            }
                        }
                    } else if (this.selectPiece(r, s)) {

                        if (this.getSelectedPiece().color === 'white' && this.getTurn() === 'white') {
                            let totalValidPieces = this.getValidPieces(this.board, this.pieces, this.turn);
                            let opponentPieces   = totalValidPieces[1];
                            let king             = totalValidPieces[2];
                            let validMoves       = this.getValidMoves(this.board, this.getSelectedPiece(), this.pieces, r, s, king, opponentPieces);

                            if (Object.keys(validMoves).length > 0) {
                                Object.keys(validMoves).forEach(key => {
                                    let vr = key.split(',')[0];
                                    let vs = key.split(',')[1];
            
                                    this.grid[vr][vs].classList.add(validMoves[key]);
                                });
                            } else {
                                this.selectedPiece = null;
                            }
                        } else {
                            this.selectedPiece = null;
                        }
                    }
                }

                const t1 = performance.now();
                console.log(`Call to handleClick() took ${t1 - t0} milliseconds.`);
            },
        },

        created() {
            this.definePiecesArray();
        },

        mounted() {
            this.squares = document.getElementsByClassName('square');

            this.defineGridArray();
            this.indexPieces();
            this.paintBoard();
            this.loadBoard();
            this.reloadGrid();
            this.setCoordinatesOfSquares();
        }
    }
</script>

<style>
    .square {
        height: 50px;
        width: 50px;
        padding: 0;
    }

    .light {
        background-color: #b5b4b3;
        border: 1px solid #b5b4b3;
    }

    .dark {
        background-color: #70706f;
        border: 1px solid #70706f;
    }

    .square svg {
        height: 100%;
        width: 100%;
        padding: 7.5px;
    }

    /**
    * The below classes use div because they need higher specificity to take precedence over the previous-move class
    */
    div.highlighted {
        border: 4px solid #e3d756;
    }

    div.capture {
        border: 4px solid #e64949;
    }

    div.castle {
        border: 4px solid #4cb2e6;
    }

    .previous-move {
        border: 4px solid #91c472;
    }

    @media screen and (max-width: 769px) {
        #board {
            width: 90vw;
        }

        .square {
            height: calc(90vw / 8);
            width: calc(90vw / 8);
        }

        .square svg {
            padding: 5px;
        }

        .captured-tray {
            width: 90vw;
        }
    }
</style>