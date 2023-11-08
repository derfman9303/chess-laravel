<template>
    <div
        id="board"
        class="position-absolute top-50 start-50 translate-middle"
        @click="handleClick"
    >
        <div class="row d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="row d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="row d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="row d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="row d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="row d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="row d-flex flex-nowrap">
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
            <div class="square"></div>
        </div>
        <div class="row d-flex flex-nowrap">
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
            handleClick(event) {
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

                        if (this.grid[r][s].classList.contains("highlighted") || this.grid[r][s].classList.contains("capture")) {
                            this.movePiece(r, s);
                            this.reloadGrid();
                            // this.switchTurns();
                        } else if (this.grid[r][s].classList.contains("castle")) {
                            this.castle(r, s);
                            // this.switchTurns();
                        }

                        this.selectedPiece = null;
                        this.removeHighlighting();

                        // AI makes move
                        if (!this.turn) {
                            const move   = this.getMove(this.board, this.pieces, this.turn, 3);
                            const index  = parseInt(move[0]);
                            const row    = parseInt(move[1]);
                            const square = parseInt(move[2]);

                            if (move !== false) {
                                // Wait 1 second before moving piece on the screen, to make it feel more natural
                                setTimeout(function() {
                                    if (this.validCastle(this.pieces[this.board[0][4]], this.pieces, this.board, row, square)) {
                                        this.castle(row, square, this.board[0][4], this.board, this.pieces);
                                    } else {
                                        this.movePiece(row, square, this.pieces[index], this.pieces, index, this.board);
                                    }

                                    this.switchTurns();
                                    this.reloadGrid();
                                }, 1000);
                            } else {
                                // Checkmate by white? Stalemate?
                            }
                        }
                    } else if (this.selectPiece(r, s)) {
                        console.log("selectPiece() returned true");

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

    .highlighted {
        background-color: #e3d756;
        border: 1px solid #c2b849;
    }

    .capture {
        background-color: #e64949;
        border: 1px solid #e64949;
    }

    .castle {
        background-color: #4cb2e6;
        border: 1px solid #4cb2e6;
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