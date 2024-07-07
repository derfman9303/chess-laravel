<template>
    <div
        id="board"
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
                oldR: null,
                oldS: null,
                newR: null,
                newS: null,
            }
        },

        methods: {
            reloadBoardWithResponseData(e) {
                this.board  = e.board;
                this.pieces = e.pieces;
                this.turn   = e.turn;

                this.removeHighlighting();
                this.removePreviousMoveHighlighting();
                this.addPreviousMoveHighlighting(e.oldR, e.oldS, e.newR, e.newS, this.grid);
                this.reloadGrid();
            },
        },

        props: {
            multiplayer: {
                type: Boolean,
                default: false,
            },

            rotateBoard: {
                type: Boolean,
                default: false,
            },

            playerColor: {
                type: String,
                default: "white",
            },

            gameKey: {
                type: String,
                default: "Hello",
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
            this.rotateBoardElements();
        }
    }
</script>

<style>
    #board {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

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